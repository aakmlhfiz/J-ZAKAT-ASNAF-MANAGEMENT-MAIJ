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
                    'message' => 'Required field is missing',
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
                $result['message'] = 'Field is required but missing';
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
                    $result['message'] = 'Address seems too short';
                }
                break;
        }

        if ($confidence < 50) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = "Very low confidence ({$confidence}%)";
        } elseif ($confidence < 70) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = "Low confidence ({$confidence}%) — please verify";
        } elseif ($confidence < 90) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = "Moderate confidence ({$confidence}%)";
        }

        return $result;
    }

    private function validateIC(string $field, string $value, int $confidence, array $result): array
    {
        $digits = preg_replace('/\D/', '', $value);

        if (! preg_match('/^\d{12}$/', $digits)) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = 'Invalid IC format — should be 12 digits';

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
            $result['message'] = 'IC has invalid month';
        }

        if ($dd < 1 || $dd > 31) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = 'IC has invalid day';
        }

        if ($stateCode < 1 || $stateCode > 59) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = 'IC has unrecognized state code';
        }

        if ($confidence < 90) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = 'IC confidence low — please verify digits';
        }

        return $result;
    }

    private function validatePhone(string $field, string $value, int $confidence, array $result): array
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) < 6) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = 'Phone number too short';

            return $result;
        }

        if (strlen($digits) > 13) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = 'Phone number seems too long';
        }

        if ($confidence < 70) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = 'Phone number confidence low — please verify';
        }

        return $result;
    }

    private function validateNumeric(string $field, string $value, int $confidence, array $result, float $min = 0, ?float $max = null): array
    {
        $cleaned = preg_replace('/[^0-9.]/', '', $value);

        if (! is_numeric($cleaned)) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = 'Value is not a valid number';

            return $result;
        }

        $num = (float) $cleaned;

        if ($num < $min) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = "Value is below minimum ({$min})";
        }

        if ($max !== null && $num > $max) {
            $result['status'] = 'warning';
            $result['severity'] = 'warning';
            $result['message'] = "Value exceeds maximum ({$max})";
        }

        return $result;
    }

    private function validatePostcode(string $field, string $value, int $confidence, array $result): array
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) !== 5) {
            $result['status'] = 'error';
            $result['severity'] = 'error';
            $result['message'] = 'Postcode must be 5 digits';
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
        $result['message'] = 'Value not in expected list — please verify';

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
        $result['message'] = 'Value should be Yes/No — please verify';

        return $result;
    }
}
