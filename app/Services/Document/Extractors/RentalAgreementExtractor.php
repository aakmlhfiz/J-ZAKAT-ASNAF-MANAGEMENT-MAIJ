<?php

namespace App\Services\Document\Extractors;

class RentalAgreementExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [];

        $tenant = $this->findLabeled($text, ['Nama Penyewa', 'Penyewa', 'Tenant Name', 'Tenant']);
        $fields['tenant_name'] = $this->makeField('tenant_name', $tenant['value'] ?? null, 'text', $context, $tenant['page'] ?? null, $tenant['label'] ?? null);

        $address = $this->findLabeled($text, ['Alamat Premis', 'Alamat Hartanah', 'Property Address', 'Premises Address'], true);
        $fields['property_address'] = $this->makeField('property_address', $address['value'] ?? null, 'textarea', $context, $address['page'] ?? null, $address['label'] ?? null);

        $rent = $this->moneyAfter($text, ['Sewa Bulanan', 'Kadar Sewa', 'Monthly Rental', 'Monthly Rent', 'Rent']);
        $fields['monthly_rental'] = $this->makeField('monthly_rental', $rent['value'] ?? null, 'amount', $context, $rent['page'] ?? null, $rent['label'] ?? null);

        $start = $this->dateAfter($text, ['Tarikh Mula', 'Tarikh Perjanjian', 'Start Date', 'Commencement Date']);
        $fields['start_date'] = $this->makeField('start_date', $start['value'] ?? null, 'date', $context, $start['page'] ?? null, $start['label'] ?? null);

        $end = $this->dateAfter($text, ['Tarikh Tamat', 'Tarikh Akhir', 'End Date', 'Expiry Date']);
        $fields['end_date'] = $this->makeField('end_date', $end['value'] ?? null, 'date', $context, $end['page'] ?? null, $end['label'] ?? null);

        if ($fields['start_date']['value'] !== null && $fields['end_date']['value'] !== null
            && $fields['end_date']['value'] < $fields['start_date']['value']) {
            $warnings[] = __('document_ocr.warnings.rental_dates_invalid');
        }

        $landlord = $this->findLabeled($text, ['Nama Tuan Rumah', 'Tuan Rumah', 'Landlord', 'Owner']);
        $fields['landlord_name'] = $this->makeField('landlord_name', $landlord['value'] ?? null, 'text', $context, $landlord['page'] ?? null, $landlord['label'] ?? null);

        if (($fields['monthly_rental']['value'] ?? null) === null) {
            $warnings[] = __('document_ocr.warnings.rental_amount_not_detected');
        }

        return $this->result($fields, $warnings, ['document_type' => 'rental_agreement']);
    }
}
