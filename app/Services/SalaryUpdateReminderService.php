<?php

namespace App\Services;

use App\Mail\SalaryUpdateReminderMail;
use App\Models\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SalaryUpdateReminderService
{
    public const DEFAULT_MONTHS = 12;

    /**
     * Number of months after approval before a reminder is due.
     */
    public static function months(): int
    {
        $months = (int) env('SALARY_REMINDER_MONTHS', self::DEFAULT_MONTHS);

        return $months > 0 ? $months : self::DEFAULT_MONTHS;
    }

    /**
     * Approved applications older than the configured threshold that have
     * not yet received a salary-update reminder.
     */
    public static function dueApplications()
    {
        return Application::query()
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->where('approved_at', '<=', now()->subMonths(self::months()))
            ->whereNull('salary_reminder_sent_at')
            ->with('user')
            ->get();
    }

    /**
     * Send the salary-update reminder (email) to every due applicant.
     */
    public static function sendReminders(): int
    {
        $sent = 0;

        foreach (self::dueApplications() as $application) {
            if (self::sendForApplication($application)) {
                $sent++;
            }
        }

        return $sent;
    }

    private static function sendForApplication(Application $application): bool
    {
        $user = $application->user;

        $email = $user->email ?? $application->email;

        if (! $email || ($user !== null && ! $user->notification_email)) {
            return false;
        }

        try {
            Mail::to($email)->send(new SalaryUpdateReminderMail($application, self::months()));
        } catch (\Throwable $e) {
            Log::error('[SalaryReminder] Gagal hantar emel untuk '.$application->application_no.': '.$e->getMessage());

            return false;
        }

        $application->update(['salary_reminder_sent_at' => now()]);

        return true;
    }
}
