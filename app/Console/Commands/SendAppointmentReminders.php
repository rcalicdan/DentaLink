<?php

namespace App\Console\Commands;

use App\Services\AppointmentReminderService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders 
                            {--sms-only : Send SMS only}
                            {--email-only : Send email only}';

    protected $description = 'Send SMS and email reminders to patients with appointments scheduled for today';

    public function __construct(
        private AppointmentReminderService $reminderService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $sendSms = !$this->option('email-only');
        $sendEmail = !$this->option('sms-only');

        $appointments = $this->reminderService->getTodaysAppointments($sendSms, $sendEmail);

        if ($appointments->isEmpty()) {
            $this->info('No pending appointments scheduled for today.');
            return 0;
        }

        $this->displayStartMessage($appointments->count(), $sendSms, $sendEmail);

        $smsResults = [];
        $emailResults = [];

        if ($sendSms) {
            $this->info('📱 Sending SMS reminders...');
            $smsResults = $this->reminderService->sendSmsReminders($appointments);
            $this->displayProgress($smsResults);
            $this->newLine();
        }

        if ($sendEmail) {
            $this->info('📧 Sending email reminders...');
            $emailResults = $this->reminderService->sendEmailReminders($appointments);
            $this->displayProgress($emailResults);
            $this->newLine();
        }

        $this->displaySummary($smsResults, $emailResults);

        return 0;
    }

    private function displayStartMessage(int $count, bool $sendSms, bool $sendEmail): void
    {
        $this->info("Found {$count} pending appointment(s) for today.");

        $notificationTypes = [];
        if ($sendSms) $notificationTypes[] = 'SMS';
        if ($sendEmail) $notificationTypes[] = 'email';
        
        $this->info('Sending ' . implode(' and ', $notificationTypes) . ' reminders...');
        $this->newLine();
    }

    private function displayProgress(array $results): void
    {
        foreach ($results as $result) {
            if ($result['status'] === 'fulfilled') {
                $value = $result['value'];
                $icon = $value['success'] ? '✓' : '✗';
                $status = $value['success'] ? 'sent' : 'failed';
                $method = $value['success'] ? 'info' : 'error';
                
                $message = "  {$icon} {$status} to {$value['patient']} ({$value['recipient']})";
                
                if (!$value['success'] && isset($value['error'])) {
                    $message .= ": {$value['error']}";
                }
                
                $this->{$method}($message);
            }
        }
    }

    private function displaySummary(array $smsResults, array $emailResults): void
    {
        $this->info("=== Summary ===");

        if (!empty($smsResults)) {
            $this->displayChannelSummary('SMS', $smsResults);
        }

        if (!empty($emailResults)) {
            if (!empty($smsResults)) {
                $this->newLine();
            }
            $this->displayChannelSummary('Email', $emailResults);
        }
    }

    private function displayChannelSummary(string $channel, array $results): void
    {
        $stats = $this->reminderService->calculateStats($results);

        $this->info("{$channel}:");
        $this->info("  Total: {$stats['total']}");
        $this->info("  Successful: {$stats['success']}");

        if ($stats['failed'] > 0) {
            $this->warn("  Failed: {$stats['failed']}");
        } else {
            $this->info("  Failed: {$stats['failed']}");
        }

        if ($stats['total'] > 0) {
            $successRate = round(($stats['success'] / $stats['total']) * 100, 2);
            $this->info("  Success rate: {$successRate}%");
        }

        if (!empty($stats['failed_items'])) {
            $this->newLine();
            $this->warn("Failed {$channel}:");
            foreach ($stats['failed_items'] as $failed) {
                $this->line("  - {$failed['patient']} ({$failed['recipient']}): {$failed['reason']}");
            }
        }
    }
}