<?php

namespace App\Enums;

enum DocumentType: string
{
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

    public function label(): string
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
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public static function options(): array
    {
        $opts = [];
        foreach (self::cases() as $case) {
            $opts[$case->value] = $case->label();
        }

        return $opts;
    }
}
