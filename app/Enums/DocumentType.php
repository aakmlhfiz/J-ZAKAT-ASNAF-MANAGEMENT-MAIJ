<?php

namespace App\Enums;

use App\Services\Document\Extractors\BankStatementExtractor;
use App\Services\Document\Extractors\BirthCertificateExtractor;
use App\Services\Document\Extractors\EmployerLetterExtractor;
use App\Services\Document\Extractors\GenericDocumentExtractor;
use App\Services\Document\Extractors\MedicalDocumentExtractor;
use App\Services\Document\Extractors\MyKadExtractor;
use App\Services\Document\Extractors\RentalAgreementExtractor;
use App\Services\Document\Extractors\SalarySlipExtractor;
use App\Services\Document\Extractors\UtilityBillExtractor;

enum DocumentType: string
{
    // Existing values kept for backwards compatibility with the applicant
    // document-request workflow and already stored document_imports rows.
    case IdentityCard = 'ic';
    case SalarySlip = 'salary_slip';
    case BankStatement = 'bank_statement';
    case UtilityBill = 'utility_bill';
    case RentalAgreement = 'rental_agreement';
    case IncomeVerificationLetter = 'income_verification_letter';
    case DivorceCertificate = 'divorce_certificate';
    case DeathCertificate = 'death_certificate';
    case OkuCard = 'oku_card';
    case StudentVerificationLetter = 'student_verification_letter';
    case OtherDocument = 'other';

    // Supporting-document OCR additions.
    case EmployerLetter = 'employer_letter';
    case MedicalDocument = 'medical_document';
    case BirthCertificate = 'birth_certificate';
    case BorangBantuan = 'borang_bantuan_maij';

    /**
     * Map legacy document type values already stored in document_imports.
     */
    public static function fromStored(?string $value): self
    {
        return match ($value) {
            'mykad', 'ic', 'identity_card', 'identity_card_copy' => self::IdentityCard,
            'salary', 'salary_slip', 'payslip' => self::SalarySlip,
            'employer_letter', 'employment_letter' => self::EmployerLetter,
            'income_verification_letter' => self::IncomeVerificationLetter,
            'bank_statement', 'bank' => self::BankStatement,
            'utility_bill', 'utility', 'bill' => self::UtilityBill,
            'rental_agreement', 'rental', 'tenancy' => self::RentalAgreement,
            'medical_document', 'medical' => self::MedicalDocument,
            'birth_certificate', 'birth' => self::BirthCertificate,
            'divorce_certificate' => self::DivorceCertificate,
            'death_certificate' => self::DeathCertificate,
            'oku_card' => self::OkuCard,
            'student_verification_letter' => self::StudentVerificationLetter,
            'borang_bantuan_maij' => self::BorangBantuan,
            default => self::OtherDocument,
        };
    }

    public function label(): string
    {
        $key = 'document_ocr.types.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->fallbackLabel();
    }

    public function description(): string
    {
        $key = 'document_ocr.type_descriptions.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : '';
    }

    public function isBorang(): bool
    {
        return $this === self::BorangBantuan;
    }

    public function extractorClass(): string
    {
        return match ($this) {
            self::IdentityCard => MyKadExtractor::class,
            self::SalarySlip => SalarySlipExtractor::class,
            self::EmployerLetter, self::IncomeVerificationLetter => EmployerLetterExtractor::class,
            self::BankStatement => BankStatementExtractor::class,
            self::UtilityBill => UtilityBillExtractor::class,
            self::RentalAgreement => RentalAgreementExtractor::class,
            self::MedicalDocument => MedicalDocumentExtractor::class,
            self::BirthCertificate => BirthCertificateExtractor::class,
            default => GenericDocumentExtractor::class,
        };
    }

    /**
     * Document types offered on the applicant upload form.
     *
     * @return array<string, string>
     */
    public static function portalOptions(): array
    {
        $types = [
            self::IdentityCard,
            self::SalarySlip,
            self::IncomeVerificationLetter,
            self::EmployerLetter,
            self::BankStatement,
            self::UtilityBill,
            self::RentalAgreement,
            self::MedicalDocument,
            self::BirthCertificate,
            self::DivorceCertificate,
            self::DeathCertificate,
            self::OkuCard,
            self::StudentVerificationLetter,
            self::OtherDocument,
        ];

        $options = [];
        foreach ($types as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }

    /**
     * Legacy + current keys accepted by upload validation.
     *
     * @return array<int, string>
     */
    public static function acceptedUploadValues(): array
    {
        return array_values(array_unique([
            ...array_keys(self::portalOptions()),
            'mykad',
            'ic',
            'salary',
            'supporting',
            'other',
        ]));
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::portalOptions();
    }

    private function fallbackLabel(): string
    {
        return match ($this) {
            self::IdentityCard => 'Kad Pengenalan',
            self::SalarySlip => 'Slip Gaji',
            self::BankStatement => 'Penyata Bank',
            self::UtilityBill => 'Bil Utiliti',
            self::RentalAgreement => 'Perjanjian Sewa',
            self::IncomeVerificationLetter => 'Surat Pengesahan Pendapatan',
            self::DivorceCertificate => 'Surat Cerai',
            self::DeathCertificate => 'Sijil Kematian',
            self::OkuCard => 'Kad OKU',
            self::StudentVerificationLetter => 'Surat Pengesahan Pelajar IPT',
            self::OtherDocument => 'Dokumen Lain',
            self::EmployerLetter => 'Surat Pengesahan Majikan',
            self::MedicalDocument => 'Dokumen Perubatan',
            self::BirthCertificate => 'Sijil Lahir',
            self::BorangBantuan => 'Borang Bantuan MAIJ',
        };
    }
}
