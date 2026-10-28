<?php

namespace App\Services;

use App\Models\DocumentImport;

class ImportValidationService
{
    public function validate(DocumentImport $import): array
    {
        $extracted = $import->extracted_data ?? [];
        $results = [
            'fields' => [],
            'warnings' => [],
            'errors' => [],
            'has_critical_errors' => false,
            'validated_count' => 0,
            'total_fields' => 0,
        ];

        $expectedFields = ['nama', 'ic', 'telefon', 'alamat', 'pendapatan'];

        foreach ($extracted as $field => $data) {
            $value = $data['value'] ?? null;
            $confidence = $data['confidence'] ?? 0;
            $fieldResult = $this->validateField($field, $value, $confidence);
            $results['fields'][$field] = $fieldResult;
            $results['total_fields']++;

            if ($fieldResult['status'] === 'valid') {
                $results['validated_count']++;
            }

            if ($fieldResult['severity'] === 'error') {
                $results['has_critical_errors'] = true;
                $results['errors'][] = $fieldResult;
            } elseif ($fieldResult['severity'] === 'warning') {
                $results['warnings'][] = $fieldResult;
            }
        }

        foreach ($expectedFields as $field) {
            if (! in_array($field, $results['fields'])) {
                if (isset($extracted[$field])) {
                    continue;
                }
                $results['has_critical_errors'] = true;
                $results['errors'][] = [
                    'field' => $field,
                    'status' => 'error',
                    'severity' => 'error',
                    'message' => __('imports.validation.required_missing'),
                ];
            }
        }

        return $results;
    }

    private function validateField(string $field, $value, int $confidence): array
    {
        $result = [
            'field' => $field,
            'value' => $value,
            'confidence' => $confidence,
            'status' => 'valid',
            'severity' => 'none',
            'message' => null,
        ];

        if ($value === null || $value === '') {
            $requiredFields = ['nama', 'ic'];
            if (in_array($field, $requiredFields)) {
                $result['status'] = 'error';
                $result['severity'] = 'error';
                $result['message'] = __('imports.validation.field_required_missing');
            }

            return $result;
        }

        $value = trim((string) $value);

        switch ($field) {
            case 'ic':
            case 'ic_ketua_rumah':
                return $this->validateIC($field, $value, $confidence, $result);

            case 'telefon':
            case 'no_telefon_bimbit':
            case 'no_telefon_rumah':
                return $this->validatePhone($field, $value, $confidence, $result);

            case 'pendapatan':
            case 'pendapatan_pasangan':
            case 'pendapatan_isi_rumah':
            case 'sewa':
            case 'utiliti':
            case 'jumlah_bantuan_diperlukan':
                return $this->validateNumeric($field, $value, $confidence, $result, 0);

            case 'bilangan_tanggungan':
                return $this->validateNumeric($field, $value, $confidence, $result, 0, 30);

            case 'poskod':
                return $this->validatePostcode($field, $value, $confidence, $result);

            case 'status_perkahwinan':
                return $this->validateEnum($field, $value, $confidence, $result, ['Bujang', 'Berkahwin', 'Duda', 'Janda', 'Bercerai', 'Balu', 'Single', 'Married', 'Divorced', 'Widowed']);

            case 'perumahan':
                return $this->validateEnum($field, $value, $confidence, $result, ['Sendiri', 'Sewa', 'Own', 'Rent']);

            case 'merokok':
                return $this->validateBoolean($field, $value, $confidence, $result);

            case 'alamat':
                if (mb_strlen($value) < 5) {
                    $result['status'] = 'warning';
                    $result['severity'] = 'warning';
                    $result['message'] = __('imports.validation.address_too_short');
                }
                break;
        }

        if ($confidence < 50) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = __('imports.validation.very_low_confidence', ['confidence' => $confidence]);
        } elseif ($confidence < 70) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.low_confidence', ['confidence' => $confidence]);
        } elseif ($confidence < 90) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.moderate_confidence', ['confidence' => $confidence]);
        }

        return $result;
    }

    private function validateIC(string $field, string $value, int $confidence, array $result): array
    {
        $digits = preg_replace('/\D/', '', $value);

        if (! preg_match('/^\d{12}$/', $digits)) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = __('imports.validation.invalid_ic');

            return $result;
        }

        $yy = (int) substr($digits, 0, 2);
        $mm = (int) substr($digits, 2, 2);
        $dd = (int) substr($digits, 4, 2);
        $stateCode = (int) substr($digits, 6, 2);

        $year = $yy > 30 ? 1900 + $yy : 2000 + $yy;

        if ($mm < 1 || $mm > 12) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.ic_invalid_month');
        }

        if ($dd < 1 || $dd > 31) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.ic_invalid_day');
        }

        if ($stateCode < 1 || $stateCode > 59) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.ic_invalid_state');
        }

        if ($confidence < 90) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.ic_low_confidence');
        }

        return $result;
    }

    private function validatePhone(string $field, string $value, int $confidence, array $result): array
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) < 6) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = __('imports.validation.phone_too_short');

            return $result;
        }

        if (strlen($digits) > 13) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.phone_too_long');
        }

        if ($confidence < 70) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.phone_low_confidence');
        }

        return $result;
    }

    private function validateNumeric(string $field, string $value, int $confidence, array $result, float $min = 0, ?float $max = null): array
    {
        $cleaned = preg_replace('/[^0-9.]/', '', $value);

        if (! is_numeric($cleaned)) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = __('imports.validation.not_a_number');

            return $result;
        }

        $num = (float) $cleaned;

        if ($num < $min) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.below_minimum', ['min' => $min]);
        }

        if ($max !== null && $num > $max) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = __('imports.validation.exceeds_maximum', ['max' => $max]);
        }

        return $result;
    }

    private function validatePostcode(string $field, string $value, int $confidence, array $result): array
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) !== 5) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = __('imports.validation.postcode_invalid');
        }

        return $result;
    }

    private function validateEnum(string $field, string $value, int $confidence, array $result, array $validValues): array
    {
        $lower = mb_strtolower($value);

        foreach ($validValues as $valid) {
            if (mb_strtolower($valid) === $lower) {
                return $result;
            }
        }

        $result['status'] = 'warning';
        $result['severity'] = 'warning';
        $result['message'] = __('imports.validation.not_in_list');

        return $result;
    }

    private function validateBoolean(string $field, string $value, int $confidence, array $result): array
    {
        $lower = mb_strtolower($value);
        $valid = ['ya', 'tidak', 'yes', 'no', 'y', 'n', 't', 'true', 'false', '1', '0'];

        foreach ($valid as $v) {
            if ($lower === $v) {
                return $result;
            }
        }

        $result['status'] = 'warning';
        $result['severity'] = 'warning';
        $result['message'] = __('imports.validation.should_be_yes_no');

        return $result;
    }
}
