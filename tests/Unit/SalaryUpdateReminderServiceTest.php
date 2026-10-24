<?php

namespace Tests\Unit;

use App\Mail\SalaryUpdateReminderMail;
use App\Models\Application;
use App\Models\PortalUser;
use App\Services\SalaryUpdateReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SalaryUpdateReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeApplication(PortalUser $user, array $attrs = []): Application
    {
        return Application::create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Ali Bin Abu',
            'ic_no' => '900101011234',
            'phone' => $user->phone,
            'email' => $user->email,
            'status' => 'approved',
        ], $attrs));
    }

    public function test_due_applications_filters_correctly(): void
    {
        $user = PortalUser::factory()->create();

        $due = $this->makeApplication($user, ['approved_at' => now()->subMonths(13)]);
        $recent = $this->makeApplication($user, ['approved_at' => now()->subMonths(6)]);
        $alreadySent = $this->makeApplication($user, [
            'approved_at' => now()->subMonths(13),
            'salary_reminder_sent_at' => now()->subMonths(1),
        ]);
        $notApproved = $this->makeApplication($user, [
            'approved_at' => now()->subMonths(13),
            'status' => 'submitted',
        ]);

        $dueIds = SalaryUpdateReminderService::dueApplications()->pluck('id')->all();

        $this->assertContains($due->id, $dueIds);
        $this->assertNotContains($recent->id, $dueIds);
        $this->assertNotContains($alreadySent->id, $dueIds);
        $this->assertNotContains($notApproved->id, $dueIds);
    }

    public function test_send_reminders_sends_email_and_marks_sent(): void
    {
        Mail::fake();

        $user = PortalUser::factory()->create(['notification_email' => true]);

        $application = $this->makeApplication($user, ['approved_at' => now()->subMonths(13)]);

        $count = SalaryUpdateReminderService::sendReminders();

        $this->assertSame(1, $count);

        Mail::assertSent(SalaryUpdateReminderMail::class, function ($mail) use ($application) {
            return $mail->application->id === $application->id;
        });

        $application->refresh();
        $this->assertNotNull($application->salary_reminder_sent_at);
    }

    public function test_send_reminders_skips_when_email_disabled(): void
    {
        Mail::fake();

        $user = PortalUser::factory()->create(['notification_email' => false]);

        $application = $this->makeApplication($user, ['approved_at' => now()->subMonths(13)]);

        $count = SalaryUpdateReminderService::sendReminders();

        $this->assertSame(0, $count);
        Mail::assertNothingSent();

        $application->refresh();
        $this->assertNull($application->salary_reminder_sent_at);
    }
}
