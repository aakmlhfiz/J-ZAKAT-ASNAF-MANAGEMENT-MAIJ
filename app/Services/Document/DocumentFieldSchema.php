<?php

namespace App\Services\Document;

use App\Enums\DocumentType;

class DocumentFieldSchema
{
    /**
     * Field definitions grouped for the review UI.
     *
     * type: text|textarea|amount|date|integer|select
     * apply: optional mapping hint used by DocumentVerificationService.
     *
     * @return array<string, array{label:string, fields:array<string, array<string, mixed>>}>
     */
    public static function groups(DocumentType $type): array
    {
        $fields = self::fields($type);
        $groups = [];

        foreach ($fields as $key => $field) {
            $group = $field['group'] ?? 'document';
            $groups[$group] ??= [
                'label' => __('document_ocr.groups.'.$group),
                'fields' => [],
            ];
            $groups[$group]['fields'][$key] = $field;
        }

        return $groups;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function fields(DocumentType $type): array
    {
        return match ($type) {
            DocumentType::IdentityCard => [
                'full_name' => self::field('full_name', 'text', 'identity', true, ['user' => 'name', 'profile' => 'nama']),
                'ic_number' => self::field('ic_number', 'text', 'identity', true, ['user' => 'ic_no'], true),
                'address' => self::field('address', 'textarea', 'identity', false, ['user' => 'address', 'profile' => 'alamat']),
                'postcode' => self::field('postcode', 'text', 'identity', false, ['user' => 'postcode', 'profile' => 'poskod']),
                'state' => self::field('state', 'text', 'identity', false, ['user' => 'state', 'profile' => 'state']),
            ],
            DocumentType::SalarySlip => [
                'employee_name' => self::field('employee_name', 'text', 'employment', false),
                'employer_name' => self::field('employer_name', 'text', 'employment', false),
                'salary_month' => self::field('salary_month', 'text', 'employment', false),
                'basic_salary' => self::field('basic_salary', 'amount', 'income', false, ['profile' => 'pendapatan', 'application' => 'monthly_income']),
                'allowances' => self::field('allowances', 'amount', 'income', false),
                'overtime' => self::field('overtime', 'amount', 'income', false),
                'gross_salary' => self::field('gross_salary', 'amount', 'income', false, ['profile' => 'pendapatan', 'application' => 'monthly_income']),
                'epf' => self::field('epf', 'amount', 'deductions', false),
                'socso' => self::field('socso', 'amount', 'deductions', false),
                'eis' => self::field('eis', 'amount', 'deductions', false),
                'other_deductions' => self::field('other_deductions', 'amount', 'deductions', false),
                'net_salary' => self::field('net_salary', 'amount', 'income', false, ['profile' => 'pendapatan', 'application' => 'monthly_income']),
            ],
            DocumentType::EmployerLetter, DocumentType::IncomeVerificationLetter => [
                'employee_name' => self::field('employee_name', 'text', 'employment', true),
                'employer_name' => self::field('employer_name', 'text', 'employment', true),
                'position' => self::field('position', 'text', 'employment', false),
                'monthly_salary' => self::field('monthly_salary', 'amount', 'income', false, ['profile' => 'pendapatan', 'application' => 'monthly_income']),
                'employment_start_date' => self::field('employment_start_date', 'date', 'employment', false),
            ],
            DocumentType::BankStatement => [
                'account_holder_name' => self::field('account_holder_name', 'text', 'bank', false),
                'bank_name' => self::field('bank_name', 'text', 'bank', false),
                'statement_period' => self::field('statement_period', 'text', 'bank', false),
                'opening_balance' => self::field('opening_balance', 'amount', 'bank', false),
                'closing_balance' => self::field('closing_balance', 'amount', 'bank', false),
            ],
            DocumentType::UtilityBill => [
                'customer_name' => self::field('customer_name', 'text', 'utility', false),
                'provider' => self::field('provider', 'text', 'utility', false),
                'account_number' => self::field('account_number', 'text', 'utility', false, [], true),
                'billing_address' => self::field('billing_address', 'textarea', 'utility', false),
                'billing_period' => self::field('billing_period', 'text', 'utility', false),
                'total_amount' => self::field('total_amount', 'amount', 'utility', false, ['profile' => 'utiliti']),
                'due_date' => self::field('due_date', 'date', 'utility', false),
            ],
            DocumentType::RentalAgreement => [
                'tenant_name' => self::field('tenant_name', 'text', 'rental', false),
                'property_address' => self::field('property_address', 'textarea', 'rental', false),
                'monthly_rental' => self::field('monthly_rental', 'amount', 'rental', false, ['profile' => 'sewa']),
                'start_date' => self::field('start_date', 'date', 'rental', false),
                'end_date' => self::field('end_date', 'date', 'rental', false),
                'landlord_name' => self::field('landlord_name', 'text', 'rental', false),
            ],
            DocumentType::MedicalDocument => [
                'patient_name' => self::field('patient_name', 'text', 'medical', false),
                'hospital_clinic' => self::field('hospital_clinic', 'text', 'medical', false),
                'document_date' => self::field('document_date', 'date', 'medical', false),
                'medical_expenses' => self::field('medical_expenses', 'amount', 'medical', false, ['profile' => 'perubatan_input']),
                'notes' => self::field('notes', 'textarea', 'medical', false),
            ],
            DocumentType::BirthCertificate => [
                'child_name' => self::field('child_name', 'text', 'birth', false),
                'child_ic' => self::field('child_ic', 'text', 'birth', false, [], true),
                'date_of_birth' => self::field('date_of_birth', 'date', 'birth', false),
                'place_of_birth' => self::field('place_of_birth', 'text', 'birth', false),
                'father_name' => self::field('father_name', 'text', 'birth', false),
                'mother_name' => self::field('mother_name', 'text', 'birth', false),
            ],
            DocumentType::OtherDocument => [
                'title' => self::field('title', 'text', 'document', false),
                'reference_no' => self::field('reference_no', 'text', 'document', false),
                'document_date' => self::field('document_date', 'date', 'document', false),
                'amount' => self::field('amount', 'amount', 'document', false),
                'notes' => self::field('notes', 'textarea', 'document', false),
            ],
            DocumentType::BorangBantuan => [],
        };
    }

    /**
     * @param  array<string, mixed>  $apply
     */
    private static function field(
        string $key,
        string $type,
        string $group,
        bool $required = false,
        array $apply = [],
        bool $sensitive = false,
    ): array {
        return [
            'key' => $key,
            'label' => __('document_ocr.fields.'.$key),
            'type' => $type,
            'group' => $group,
            'required' => $required,
            'apply' => $apply,
            'sensitive' => $sensitive,
        ];
    }

    public static function label(DocumentType $type, string $key): string
    {
        return self::fields($type)[$key]['label'] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function type(DocumentType $type, string $key): string
    {
        return self::fields($type)[$key]['type'] ?? 'text';
    }

    public static function isSensitive(DocumentType $type, string $key): bool
    {
        return (bool) (self::fields($type)[$key]['sensitive'] ?? false);
    }
}
