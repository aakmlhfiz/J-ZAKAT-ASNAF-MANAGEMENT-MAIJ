<?php

namespace App\Services\Document\Extractors;

class BankStatementExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [
            __('document_ocr.warnings.bank_credits_not_income'),
        ];

        $holder = $this->findLabeled($text, ['Nama Pemegang Akaun', 'Account Holder', 'Account Name', 'Nama Akaun']);
        $fields['account_holder_name'] = $this->makeField('account_holder_name', $holder['value'] ?? null, 'text', $context, $holder['page'] ?? null, $holder['label'] ?? null);

        $bank = $this->findLabeled($text, ['Nama Bank', 'Bank Name', 'Bank']);
        $fields['bank_name'] = $this->makeField('bank_name', $bank['value'] ?? null, 'text', $context, $bank['page'] ?? null, $bank['label'] ?? null);

        $period = $this->findLabeled($text, ['Tempoh Penyata', 'Penyata Bagi Tempoh', 'Statement Period', 'Period']);
        $fields['statement_period'] = $this->makeField('statement_period', $period['value'] ?? null, 'text', $context, $period['page'] ?? null, $period['label'] ?? null);

        $opening = $this->moneyAfter($text, ['Baki Permulaan', 'Opening Balance', 'Balance Brought Forward']);
        $fields['opening_balance'] = $this->makeField('opening_balance', $opening['value'] ?? null, 'amount', $context, $opening['page'] ?? null, $opening['label'] ?? null);

        $closing = $this->moneyAfter($text, ['Baki Penutup', 'Closing Balance', 'Ending Balance']);
        $fields['closing_balance'] = $this->makeField('closing_balance', $closing['value'] ?? null, 'amount', $context, $closing['page'] ?? null, $closing['label'] ?? null);

        $transactions = $this->transactions($text);
        if ($transactions !== []) {
            $warnings[] = __('document_ocr.warnings.bank_transactions_best_effort', ['count' => count($transactions)]);
        }

        return $this->result($fields, $warnings, [
            'document_type' => 'bank_statement',
            'transactions' => $transactions,
        ]);
    }

    /**
     * Best-effort transaction line extraction. Never promotes a credit to income.
     *
     * @return array<int, array{date:string, description:string, amount:string, type:string}>
     */
    private function transactions(string $text): array
    {
        $transactions = [];
        $datePattern = '/(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})/u';

        foreach ($this->lines($text) as $line) {
            $lineText = $line['text'];
            if ($lineText === '' || ! preg_match($datePattern, $lineText, $dateMatch)) {
                continue;
            }

            if (preg_match('/baki|balance|jumlah|total/iu', $lineText)) {
                continue;
            }

            if (! preg_match('/(?:RM|MYR)?\s*([0-9][0-9,]*\.[0-9]{2})\s*(CR|DR)?/iu', $lineText, $amountMatch)) {
                continue;
            }

            $description = trim(str_replace($dateMatch[0], '', $lineText));
            $description = trim(preg_replace('/(?:RM|MYR)?\s*[0-9][0-9,]*\.[0-9]{2}\s*(CR|DR)?/iu', '', $description) ?? '');
            if ($description === '') {
                continue;
            }

            $typeToken = '';
            if (preg_match('/\b(CR|DR|CREDIT|DEBIT)\b/iu', $lineText, $typeMatch)) {
                $typeToken = strtoupper($typeMatch[1]);
            }
            $type = in_array($typeToken, ['CR', 'CREDIT'], true)
                ? 'credit'
                : (in_array($typeToken, ['DR', 'DEBIT'], true) ? 'debit' : 'unspecified');
            $normalized = $this->normalizer->normalize('amount', $amountMatch[1], 'amount');

            if ($normalized['value'] === null) {
                continue;
            }

            $transactions[] = [
                'date' => $dateMatch[1],
                'description' => mb_substr($description, 0, 180),
                'amount' => $normalized['value'],
                'type' => $type,
            ];

            if (count($transactions) >= 100) {
                break;
            }
        }

        return $transactions;
    }
}
