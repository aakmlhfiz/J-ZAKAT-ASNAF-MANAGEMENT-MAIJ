<?php

namespace App\Services\Document;

use App\Enums\DocumentType;
use App\Models\Admin;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\DocumentImport;
use App\Models\DocumentVerifiedValue;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use App\Services\EligibilityService;
use App\Services\HouseholdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentVerificationService
{
    public function verify(
        DocumentImport $import,
        array $input,
        Admin|PortalUser $actor,
        Request $request,
    ): array {
        if (! in_array($import->status, ['needs_review', 'extracted'], true)) {
            throw ValidationException::withMessages([
                'document' => __('document_ocr.validation.not_ready', ['status' => $import->statusLabel()]),
            ]);
        }

        $type = $import->type();
        $schema = DocumentFieldSchema::fields($type);
        $fields = $this->validateFields($import, $schema, $input);

        $applyProfile = (bool) ($input['apply_profile'] ?? false);
        $applyApplication = (bool) ($input['apply_application'] ?? false);
        $applyHousehold = (bool) ($input['apply_household'] ?? false);
        $replaceExisting = (bool) ($input['replace_existing'] ?? false);
        $incomeField = (string) ($input['income_field'] ?? $this->defaultIncomeField($type));

        $profile = $this->resolveProfile($import, $actor);
        $application = $this->resolveApplication($import, $actor);
        $householdMember = $profile?->householdMember;
        $household = $householdMember?->household ?? $import->household;

        $applied = [];
        $skipped = [];

        DB::transaction(function () use (
            $import, $type, $fields, $actor, $request, $profile, $application,
            $householdMember, $household, $applyProfile, $applyApplication,
            $applyHousehold, $replaceExisting, $incomeField, &$applied, &$skipped
        ) {
            $verifiedData = [
                'fields' => $fields,
                'applied' => [],
                'skipped' => [],
            ];

            if ($actor instanceof PortalUser) {
                $userApplied = $this->applyToPortalUser($import, $actor, $fields, $replaceExisting, $applied, $skipped);
                if ($userApplied) {
                    $verifiedData['applied'][] = $userApplied;
                }
            }

            if ($applyProfile && $profile) {
                $this->applyToProfile($type, $fields, $profile, $householdMember, $incomeField, $replaceExisting, $applied, $skipped);
            }

            if ($applyApplication && $application) {
                $this->applyToApplication($type, $fields, $application, $incomeField, $replaceExisting, $applied, $skipped);
            }

            if ($applyHousehold && $household) {
                $this->applyToHousehold($fields, $household, $type, $incomeField);
                $applied[] = ['target' => 'household', 'id' => $household->id];
            }

            if ($profile) {
                EligibilityService::apply($profile);
                $profile->save();
            }

            if ($household && $applyHousehold) {
                app(HouseholdService::class)->updateAggregates($household);
            }

            $verifiedData['applied'] = $applied;
            $verifiedData['skipped'] = $skipped;

            $this->replaceVerifiedValues($import, $fields, $actor, $applied);

            $payload = [
                'status' => 'committed',
                'verified_at' => now(),
                'verified_data' => $verifiedData,
                'rejection_reason' => null,
            ];

            if ($actor instanceof Admin) {
                $payload['verified_by_admin_id'] = $actor->id;
                $payload['reviewed_at'] = now();
                $payload['reviewed_by_admin_id'] = $actor->id;
            } else {
                $payload['verified_by_portal_user_id'] = $actor->id;
            }

            if ($profile) {
                $payload['profil_asnaf_ic'] = $profile->ic;
            }
            if ($application) {
                $payload['application_id'] = $application->id;
            }
            if ($household) {
                $payload['household_id'] = $household->id;
            }

            $import->update($payload);

            $this->audit($import, $actor, $request, 'document_verified', [
                'document_type' => $type->value,
                'fields_verified' => array_keys(array_filter($fields, fn ($field) => $field['value'] !== null)),
                'applied' => $applied,
                'skipped' => $skipped,
            ]);
        });

        return [
            'import' => $import->fresh(),
            'applied' => $applied,
            'skipped' => $skipped,
        ];
    }

    public function reject(DocumentImport $import, string $reason, Admin $admin, Request $request): DocumentImport
    {
        $import->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by_admin_id' => $admin->id,
        ]);

        $this->audit($import, $admin, $request, 'document_rejected', [
            'document_type' => $import->type()->value,
            'reason' => $reason,
        ]);

        return $import->fresh();
    }

    /**
     * @param  array<string, array<string, mixed>>  $schema
     * @param  array<string, mixed>  $input
     * @return array<string, array<string, mixed>>
     */
    private function validateFields(DocumentImport $import, array $schema, array $input): array
    {
        $submitted = $input['fields'] ?? [];
        if (! is_array($submitted)) {
            throw ValidationException::withMessages(['fields' => __('document_ocr.validation.fields_invalid')]);
        }

        $original = $import->ocr_result['extracted_data'] ?? $import->extracted_data ?? [];
        $verified = [];
        $errors = [];

        foreach ($schema as $key => $definition) {
            $raw = $submitted[$key] ?? null;
            $raw = is_string($raw) ? trim($raw) : $raw;
            $value = ($raw === '' || $raw === null) ? null : $raw;
            $label = $definition['label'];

            if (($definition['required'] ?? false) && $value === null) {
                $errors['fields.'.$key] = __('document_ocr.validation.required', ['field' => $label]);
            }

            if ($value !== null) {
                $value = $this->normalizeValue($definition['type'], $value);
                if ($value === null) {
                    $errors['fields.'.$key] = __('document_ocr.validation.invalid', ['field' => $label]);
                } elseif ($import->type() === DocumentType::IdentityCard && $key === 'ic_number'
                    && strlen(preg_replace('/\D/', '', (string) $value) ?? '') !== 12) {
                    $errors['fields.'.$key] = __('document_ocr.validation.invalid', ['field' => $label]);
                }
            }

            $source = $original[$key] ?? [];
            $verified[$key] = [
                'value' => $value,
                'original_value' => $source['original_value'] ?? $source['value'] ?? null,
                'confidence' => $source['confidence'] ?? null,
                'confidence_source' => $source['confidence_source'] ?? null,
                'source_page' => $source['page'] ?? null,
                'group' => $definition['group'] ?? 'document',
                'type' => $definition['type'] ?? 'text',
                'verified' => $value !== null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $verified;
    }

    private function normalizeValue(string $type, mixed $value): ?string
    {
        if (is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        return match ($type) {
            'amount' => is_numeric(str_replace(',', '', $value)) && (float) str_replace(',', '', $value) >= 0
                ? number_format((float) str_replace(',', '', $value), 2, '.', '')
                : null,
            'date' => $this->normalizeDate($value),
            'integer' => ctype_digit($value) ? (string) (int) $value : (is_numeric($value) && (int) $value >= 0 ? (string) (int) $value : null),
            default => mb_strlen($value) <= 2000 ? $value : mb_substr($value, 0, 2000),
        };
    }

    private function normalizeDate(string $value): ?string
    {
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'm/Y', 'Y-m'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveProfile(DocumentImport $import, Admin|PortalUser $actor): ?ProfilAsnaf
    {
        if ($import->profilAsnaf) {
            return $import->profilAsnaf;
        }

        $ic = $import->extracted_data['ic_number']['value'] ?? null;
        if (! $ic && $actor instanceof PortalUser) {
            $ic = $actor->ic_no;
        }

        return $ic ? ProfilAsnaf::where('ic', preg_replace('/\D/', '', (string) $ic))->first() : null;
    }

    private function resolveApplication(DocumentImport $import, Admin|PortalUser $actor): ?Application
    {
        if ($import->application) {
            return $import->application;
        }

        return null;
    }

    private function defaultIncomeField(DocumentType $type): string
    {
        return match ($type) {
            DocumentType::EmployerLetter, DocumentType::IncomeVerificationLetter => 'monthly_salary',
            default => 'gross_salary',
        };
    }

    private function incomeValue(DocumentType $type, array $fields, string $preferred): ?float
    {
        $keys = in_array($type, [DocumentType::EmployerLetter, DocumentType::IncomeVerificationLetter], true)
            ? ['monthly_salary']
            : array_values(array_unique([$preferred, 'gross_salary', 'net_salary', 'basic_salary']));

        foreach ($keys as $key) {
            $value = $fields[$key]['value'] ?? null;
            if ($value !== null && is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    private function applyToPortalUser(
        DocumentImport $import,
        PortalUser $user,
        array $fields,
        bool $replaceExisting,
        array &$applied,
        array &$skipped,
    ): ?array {
        if ($import->type() !== DocumentType::IdentityCard) {
            return null;
        }

        $map = [
            'full_name' => 'name',
            'ic_number' => 'ic_no',
            'address' => 'address',
            'postcode' => 'postcode',
            'state' => 'state',
        ];

        $changed = false;
        foreach ($map as $field => $column) {
            $value = $fields[$field]['value'] ?? null;
            if ($value === null) {
                continue;
            }

            if ($column === 'ic_no') {
                $digits = preg_replace('/\D/', '', (string) $value);
                $taken = PortalUser::where('ic_no', $digits)->where('id', '!=', $user->id)->exists();
                if (strlen($digits) !== 12 || $taken || ($user->ic_no && ! $replaceExisting)) {
                    continue;
                }
                $user->ic_no = $digits;
                $changed = true;
                $applied[] = ['target' => 'portal_user', 'id' => $user->id, 'field' => 'ic_no', 'source_field' => $field];

                continue;
            }

            $current = $user->{$column};
            if ($current && $current !== $value && ! $replaceExisting) {
                $skipped[] = ['target' => 'portal_user', 'field' => $column, 'reason' => 'existing_value'];

                continue;
            }
            $user->{$column} = $value;
            $changed = true;
            $applied[] = ['target' => 'portal_user', 'id' => $user->id, 'field' => $column, 'source_field' => $field];
        }

        if ($changed) {
            $user->save();
        }

        return $changed ? ['target' => 'portal_user', 'id' => $user->id] : null;
    }

    private function applyToProfile(
        DocumentType $type,
        array $fields,
        ProfilAsnaf $profile,
        ?HouseholdMember $member,
        string $incomeField,
        bool $replaceExisting,
        array &$applied,
        array &$skipped,
    ): void {
        $updates = [];

        $sourceMap = [];

        if ($type === DocumentType::IdentityCard) {
            $updates = $this->mapFields($fields, [
                'address' => 'alamat',
                'postcode' => 'poskod',
            ]);
            $sourceMap = ['alamat' => 'address', 'poskod' => 'postcode'];
        }

        if (in_array($type, [DocumentType::SalarySlip, DocumentType::EmployerLetter, DocumentType::IncomeVerificationLetter], true)) {
            $income = $this->incomeValue($type, $fields, $incomeField);
            if ($income !== null) {
                $updates['pendapatan'] = $income;
                $sourceMap['pendapatan'] = $incomeField;
                if ($member) {
                    $member->pendapatan_bulanan = $income;
                    $member->save();
                    $applied[] = ['target' => 'household_member', 'id' => $member->id, 'field' => 'pendapatan_bulanan', 'source_field' => $incomeField];
                }
            }
        }

        if ($type === DocumentType::UtilityBill && isset($fields['total_amount']['value'])) {
            $updates['utiliti'] = (float) $fields['total_amount']['value'];
            $sourceMap['utiliti'] = 'total_amount';
        }

        if ($type === DocumentType::RentalAgreement && isset($fields['monthly_rental']['value'])) {
            $updates['sewa'] = (float) $fields['monthly_rental']['value'];
            $updates['perumahan'] = 'sewa';
            $sourceMap['sewa'] = 'monthly_rental';
        }

        if ($type === DocumentType::MedicalDocument && isset($fields['medical_expenses']['value'])) {
            $updates['perubatan_input'] = (float) $fields['medical_expenses']['value'];
            $sourceMap['perubatan_input'] = 'medical_expenses';
        }

        foreach ($updates as $column => $value) {
            $current = $profile->{$column};
            if ($column === 'perumahan') {
                if ($current && $current !== $value && ! $replaceExisting) {
                    $skipped[] = ['target' => 'profil_asnaf', 'field' => $column, 'reason' => 'existing_value'];

                    continue;
                }
                $profile->{$column} = $value;
                continue;
            }
            if ($current !== null && $current != 0 && $current != $value && ! $replaceExisting) {
                $skipped[] = ['target' => 'profil_asnaf', 'field' => $column, 'reason' => 'existing_value'];

                continue;
            }
            $profile->{$column} = $value;
            $applied[] = [
                'target' => 'profil_asnaf',
                'id' => $profile->id,
                'field' => $column,
                'source_field' => $sourceMap[$column] ?? $column,
            ];
        }

        $profile->save();
    }

    private function applyToApplication(
        DocumentType $type,
        array $fields,
        Application $application,
        string $incomeField,
        bool $replaceExisting,
        array &$applied,
        array &$skipped,
    ): void {
        if (! in_array($type, [DocumentType::SalarySlip, DocumentType::EmployerLetter, DocumentType::IncomeVerificationLetter], true)) {
            return;
        }

        $income = $this->incomeValue($type, $fields, $incomeField);
        if ($income === null) {
            return;
        }

        if ((float) $application->monthly_income > 0 && (float) $application->monthly_income !== $income && ! $replaceExisting) {
            $skipped[] = ['target' => 'application', 'field' => 'monthly_income', 'reason' => 'existing_value'];

            return;
        }

        $application->monthly_income = $income;
        $application->total_household_income = $income + (float) $application->spouse_income;
        $application->save();
        $applied[] = ['target' => 'application', 'id' => $application->id, 'field' => 'monthly_income', 'source_field' => $incomeField];
    }

    private function applyToHousehold(array $fields, Household $household, DocumentType $type, string $incomeField): void
    {
        $members = $household->members()->with('profilAsnaf')->get();

        if ($members->isNotEmpty()) {
            $household->total_income = $members->sum('pendapatan_bulanan');
            $household->total_expenses = $members->sum(function (HouseholdMember $member) {
                $profile = $member->profilAsnaf;

                return $profile
                    ? (float) $profile->sewa + (float) $profile->utiliti + (float) $profile->pengangkutan_input + (float) $profile->perubatan_input
                    : 0;
            });
        }

        $household->save();
    }

    private function mapFields(array $fields, array $map): array
    {
        $updates = [];
        foreach ($map as $field => $column) {
            if (isset($fields[$field]['value']) && $fields[$field]['value'] !== null) {
                $updates[$column] = $fields[$field]['value'];
            }
        }

        return $updates;
    }

    private function replaceVerifiedValues(DocumentImport $import, array $fields, Admin|PortalUser $actor, array $applied): void
    {
        DocumentVerifiedValue::where('document_import_id', $import->id)->delete();

        foreach ($fields as $key => $field) {
            if ($field['value'] === null) {
                continue;
            }

            $target = collect($applied)->first(fn ($item) => ($item['source_field'] ?? null) === $key);

            DocumentVerifiedValue::create([
                'document_import_id' => $import->id,
                'field_key' => $key,
                'field_group' => $field['group'] ?? null,
                'value_text' => is_string($field['value']) ? $field['value'] : null,
                'value_numeric' => is_numeric($field['value']) ? $field['value'] : null,
                'value_date' => $field['type'] === 'date' ? $field['value'] : null,
                'confidence' => $field['confidence'],
                'confidence_source' => $field['confidence_source'],
                'source_page' => $field['source_page'],
                'verified_by_admin_id' => $actor instanceof Admin ? $actor->id : null,
                'verified_by_portal_user_id' => $actor instanceof PortalUser ? $actor->id : null,
                'applied_to_type' => $target['target'] ?? null,
                'applied_to_id' => $target['id'] ?? null,
            ]);
        }
    }

    private function audit(DocumentImport $import, Admin|PortalUser $actor, Request $request, string $action, array $metadata): void
    {
        AuditLog::create([
            'user_id' => $actor->id,
            'user_role' => $actor instanceof Admin ? 'admin' : 'portal',
            'user_email' => $actor->email,
            'action' => $action,
            'category' => 'document',
            'description' => __('document_ocr.audit.'.$action, ['file' => $import->original_filename]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => array_merge($metadata, [
                'document_import_id' => $import->id,
                'ic_masked' => $import->maskedIc(),
            ]),
        ]);
    }
}
