<?php

namespace App\Services\Document\Extractors;

class UtilityBillExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [];

        $customer = $this->findLabeled($text, ['Nama Pelanggan', 'Nama Akaun', 'Customer Name', 'Account Name']);
        $fields['customer_name'] = $this->makeField('customer_name', $customer['value'] ?? null, 'text', $context, $customer['page'] ?? null, $customer['label'] ?? null);

        $provider = $this->findLabeled($text, ['Pembekal', 'Provider', 'Syarikat']);
        if (($provider['value'] ?? null) === null) {
            $provider = $this->provider($text);
        }
        $fields['provider'] = $this->makeField('provider', $provider['value'] ?? null, 'text', $context, $provider['page'] ?? null, $provider['label'] ?? null);

        $account = $this->findLabeled($text, ['No. Akaun', 'No Akaun', 'Nombor Akaun', 'Account Number', 'Account No']);
        $fields['account_number'] = $this->makeField('account_number', $account['value'] ?? null, 'text', $context, $account['page'] ?? null, $account['label'] ?? null);

        $address = $this->findLabeled($text, ['Alamat Bil', 'Alamat Premis', 'Billing Address', 'Service Address'], true);
        $fields['billing_address'] = $this->makeField('billing_address', $address['value'] ?? null, 'textarea', $context, $address['page'] ?? null, $address['label'] ?? null);

        $period = $this->findLabeled($text, ['Tempoh Bil', 'Tempoh', 'Billing Period', 'Bill Period']);
        $fields['billing_period'] = $this->makeField('billing_period', $period['value'] ?? null, 'text', $context, $period['page'] ?? null, $period['label'] ?? null);

        $total = $this->moneyAfter($text, ['Jumlah Perlu Dibayar', 'Jumlah Bil', 'Jumlah', 'Total Amount', 'Total Due', 'Total']);
        $fields['total_amount'] = $this->makeField('total_amount', $total['value'] ?? null, 'amount', $context, $total['page'] ?? null, $total['label'] ?? null);

        $due = $this->dateAfter($text, ['Tarikh Akhir Bayar', 'Tarikh Bayar', 'Due Date', 'Payment Due']);
        $fields['due_date'] = $this->makeField('due_date', $due['value'] ?? null, 'date', $context, $due['page'] ?? null, $due['label'] ?? null);

        if (($fields['total_amount']['value'] ?? null) === null) {
            $warnings[] = __('document_ocr.warnings.utility_total_not_detected');
        }

        return $this->result($fields, $warnings, ['document_type' => 'utility_bill']);
    }

    /**
     * @return array{value:string, page:int, label:string}|null
     */
    private function provider(string $text): ?array
    {
        $providers = ['Tenaga Nasional', 'TNB', 'SAJ', 'Ranhill', 'Air Johor', 'SYABAS', 'Air Selangor', 'Indah Water'];
        foreach ($this->lines($text) as $line) {
            foreach ($providers as $name) {
                if (mb_stripos($line['text'], $name) !== false) {
                    return ['value' => $name, 'page' => $line['page'], 'label' => 'Provider'];
                }
            }
        }

        return null;
    }
}
