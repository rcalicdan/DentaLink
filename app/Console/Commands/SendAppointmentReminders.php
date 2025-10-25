<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Enums\AppointmentStatuses;
use App\Mail\AppointmentReminderMail;
use Hibla\HttpClient\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use function Hibla\async;
use function Hibla\await;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders 
                            {--sms-only : Send SMS only}
                            {--email-only : Send email only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS and email reminders to patients with appointments scheduled for today';

    /**
     * Maximum number of retry attempts for failed SMS
     */
    private const MAX_RETRIES = 3;

    /**
     * Base delay in seconds for exponential backoff
     */
    private const BASE_DELAY = 1.0;

    /**
     * Backoff multiplier for retry delays
     */
    private const BACKOFF_MULTIPLIER = 2.0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Determine what to send based on options
        $sendSms = !$this->option('email-only');
        $sendEmail = !$this->option('sms-only');

        // Get today's pending appointments
        $appointments = Appointment::with(['patient', 'branch'])
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', AppointmentStatuses::WAITING)
            ->whereHas('patient', function ($query) use ($sendSms, $sendEmail) {
                $query->where(function ($q) use ($sendSms, $sendEmail) {
                    if ($sendSms) {
                        $q->whereNotNull('phone');
                    }
                    if ($sendEmail) {
                        $q->orWhereNotNull('email');
                    }
                });
            })
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No pending appointments scheduled for today.');
            return 0;
        }

        $this->info("Found {$appointments->count()} pending appointment(s) for today.");
        
        $notificationTypes = [];
        if ($sendSms) $notificationTypes[] = 'SMS';
        if ($sendEmail) $notificationTypes[] = 'email';
        $this->info('Sending ' . implode(' and ', $notificationTypes) . ' reminders...');
        $this->newLine();

        // Send notifications - SMS and Email separately for better concurrency
        $smsResults = [];
        $emailResults = [];

        if ($sendSms) {
            $this->info('📱 Sending SMS reminders...');
            $smsResults = $this->sendSMSReminders($appointments);
            $this->newLine();
        }

        if ($sendEmail) {
            $this->info('📧 Sending email reminders...');
            $emailResults = $this->sendEmailReminders($appointments);
            $this->newLine();
        }

        // Display summary
        $this->displaySummary($smsResults, $emailResults);

        return 0;
    }

    /**
     * Send SMS reminders to all appointments concurrently
     *
     * @param \Illuminate\Database\Eloquent\Collection $appointments
     * @return array
     */
    private function sendSMSReminders($appointments): array
    {
        return await(async(function () use ($appointments) {
            $promises = [];

            foreach ($appointments as $appointment) {
                if ($appointment->patient->phone) {
                    $promises[$appointment->id] = $this->sendSMS($appointment);
                }
            }

            return await(\Hibla\Promise\Promise::allSettled($promises));
        }));
    }

    /**
     * Send email reminders to all appointments sequentially
     * (Email sending is blocking, so we keep it simple)
     *
     * @param \Illuminate\Database\Eloquent\Collection $appointments
     * @return array
     */
    private function sendEmailReminders($appointments): array
    {
        $results = [];

        foreach ($appointments as $appointment) {
            if ($appointment->patient->email) {
                $results[$appointment->id] = [
                    'status' => 'fulfilled',
                    'value' => $this->sendEmailSync($appointment)
                ];
            }
        }

        return $results;
    }

    /**
     * Send SMS to appointment
     *
     * @param \App\Models\Appointment $appointment
     * @return \Hibla\Promise\Interfaces\PromiseInterface
     */
    private function sendSMS($appointment)
    {
        return async(function () use ($appointment) {
            $patient = $appointment->patient;
            $phone = $this->formatPhoneNumber($patient->phone);

            try {
                $apiKey = env('SEMAPHORE_API_KEY');
                $senderName = env('SEMAPHORE_SENDER_NAME', 'DENTALINK');

                if (empty($apiKey)) {
                    throw new \Exception('Semaphore API key is not configured.');
                }

                $message = $this->buildSMSMessage($appointment);
                $this->line("  Sending to {$patient->full_name} ({$phone})");

                $response = await(
                    Http::asForm()
                        ->retry(
                            maxRetries: self::MAX_RETRIES,
                            baseDelay: self::BASE_DELAY,
                            backoffMultiplier: self::BACKOFF_MULTIPLIER
                        )
                        ->timeout(30)
                        ->post('https://api.semaphore.co/api/v4/messages', [
                            'apikey' => $apiKey,
                            'number' => $phone,
                            'message' => $message,
                            'sendername' => $senderName
                        ])
                );

                $body = $response->json();

                if (isset($body[0]['status']) && $body[0]['status'] === 'Queued') {
                    $this->info("  ✓ SMS sent to {$patient->full_name}");
                    
                    Log::info('Appointment SMS reminder sent', [
                        'appointment_id' => $appointment->id,
                        'patient' => $patient->full_name,
                        'phone' => $phone
                    ]);

                    return [
                        'success' => true,
                        'patient' => $patient->full_name,
                        'recipient' => $phone,
                        'response' => $body
                    ];
                }

                $this->warn("  ⚠ SMS unexpected status for {$patient->full_name}");
                
                Log::warning('Appointment SMS reminder unexpected status', [
                    'appointment_id' => $appointment->id,
                    'patient' => $patient->full_name,
                    'phone' => $phone,
                    'response' => $body
                ]);

                return [
                    'success' => false,
                    'patient' => $patient->full_name,
                    'recipient' => $phone,
                    'reason' => 'Unexpected status',
                    'response' => $body
                ];

            } catch (\Throwable $e) {
                $this->error("  ✗ SMS failed for {$patient->full_name}: {$e->getMessage()}");
                
                Log::error('Appointment SMS reminder failed', [
                    'appointment_id' => $appointment->id,
                    'patient' => $patient->full_name,
                    'phone' => $phone,
                    'error' => $e->getMessage()
                ]);

                return [
                    'success' => false,
                    'patient' => $patient->full_name,
                    'recipient' => $phone,
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Send email to appointment synchronously
     *
     * @param \App\Models\Appointment $appointment
     * @return array
     */
    private function sendEmailSync($appointment): array
    {
        $patient = $appointment->patient;
        $email = $patient->email;

        try {
            $this->line("  Sending to {$patient->full_name} ({$email})");

            Mail::to($email)->send(new AppointmentReminderMail($appointment));

            $this->info("  ✓ Email sent to {$patient->full_name}");
            
            Log::info('Appointment email reminder sent', [
                'appointment_id' => $appointment->id,
                'patient' => $patient->full_name,
                'email' => $email
            ]);

            return [
                'success' => true,
                'patient' => $patient->full_name,
                'recipient' => $email
            ];

        } catch (\Throwable $e) {
            $this->error("  ✗ Email failed for {$patient->full_name}: {$e->getMessage()}");
            
            Log::error('Appointment email reminder failed', [
                'appointment_id' => $appointment->id,
                'patient' => $patient->full_name,
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'patient' => $patient->full_name,
                'recipient' => $email,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the SMS message for the appointment
     *
     * @param \App\Models\Appointment $appointment
     * @return string
     */
    private function buildSMSMessage(Appointment $appointment): string
    {
        $patientName = $appointment->patient->first_name;
        $date = $appointment->appointment_date->format('M d, Y');
        $branch = $appointment->branch->name ?? 'our clinic';

        // Start with basic greeting and date
        $message = "Hi {$patientName}! This is a reminder that you have a dental appointment today, {$date}";

        // Add time information if available
        $timeInfo = $this->getTimeInfo($appointment);
        if ($timeInfo) {
            $message .= " at {$timeInfo}";
        }

        // Add branch/location
        $message .= " at {$branch}";

        // Add queue number if available
        if (!empty($appointment->queue_number)) {
            $message .= ". Your queue number is #{$appointment->queue_number}";
        }

        // Closing
        $message .= ". See you soon!";

        return $message;
    }

    /**
     * Get formatted time information from appointment
     *
     * @param \App\Models\Appointment $appointment
     * @return string|null
     */
    private function getTimeInfo(Appointment $appointment): ?string
    {
        // Try formatted time range first
        if (!empty($appointment->formatted_time_range)) {
            return $appointment->formatted_time_range;
        }

        // Try start and end time
        if ($appointment->start_time && $appointment->end_time) {
            return $appointment->start_time->format('g:i A') . ' - ' . $appointment->end_time->format('g:i A');
        }

        // Try just start time
        if ($appointment->start_time) {
            return $appointment->start_time->format('g:i A');
        }

        return null;
    }

    /**
     * Format phone number to Philippine mobile format
     *
     * @param string $phone
     * @return string
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Convert to 09XXXXXXXXX format if needed
        if (strlen($phone) === 10 && strpos($phone, '9') === 0) {
            return '0' . $phone;
        }

        // Convert +639XXXXXXXXX to 09XXXXXXXXX
        if (strlen($phone) === 12 && strpos($phone, '639') === 0) {
            return '0' . substr($phone, 2);
        }

        return $phone;
    }

    /**
     * Display summary of notification results
     *
     * @param array $smsResults
     * @param array $emailResults
     * @return void
     */
    private function displaySummary(array $smsResults, array $emailResults): void
    {
        $this->info("=== Summary ===");
        
        // SMS Summary
        if (!empty($smsResults)) {
            $smsStats = $this->calculateStats($smsResults);
            
            $this->info("SMS:");
            $this->info("  Total: {$smsStats['total']}");
            $this->info("  Successful: {$smsStats['success']}");
            
            if ($smsStats['failed'] > 0) {
                $this->warn("  Failed: {$smsStats['failed']}");
            } else {
                $this->info("  Failed: {$smsStats['failed']}");
            }
            
            if ($smsStats['total'] > 0) {
                $successRate = round(($smsStats['success'] / $smsStats['total']) * 100, 2);
                $this->info("  Success rate: {$successRate}%");
            }

            // Show failed SMS
            if (!empty($smsStats['failed_items'])) {
                $this->newLine();
                $this->warn("Failed SMS:");
                foreach ($smsStats['failed_items'] as $failed) {
                    $this->line("  - {$failed['patient']} ({$failed['recipient']}): {$failed['reason']}");
                }
            }
        }

        // Email Summary
        if (!empty($emailResults)) {
            $this->newLine();
            $emailStats = $this->calculateStats($emailResults);
            
            $this->info("Email:");
            $this->info("  Total: {$emailStats['total']}");
            $this->info("  Successful: {$emailStats['success']}");
            
            if ($emailStats['failed'] > 0) {
                $this->warn("  Failed: {$emailStats['failed']}");
            } else {
                $this->info("  Failed: {$emailStats['failed']}");
            }
            
            if ($emailStats['total'] > 0) {
                $successRate = round(($emailStats['success'] / $emailStats['total']) * 100, 2);
                $this->info("  Success rate: {$successRate}%");
            }

            // Show failed emails
            if (!empty($emailStats['failed_items'])) {
                $this->newLine();
                $this->warn("Failed Emails:");
                foreach ($emailStats['failed_items'] as $failed) {
                    $this->line("  - {$failed['patient']} ({$failed['recipient']}): {$failed['reason']}");
                }
            }
        }
    }

    /**
     * Calculate statistics from results
     *
     * @param array $results
     * @return array
     */
    private function calculateStats(array $results): array
    {
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'failed_items' => []
        ];

        foreach ($results as $result) {
            if ($result['status'] === 'fulfilled') {
                $value = $result['value'];
                $stats['total']++;

                if ($value['success']) {
                    $stats['success']++;
                } else {
                    $stats['failed']++;
                    $stats['failed_items'][] = [
                        'patient' => $value['patient'],
                        'recipient' => $value['recipient'],
                        'reason' => $value['error'] ?? $value['reason'] ?? 'Unknown'
                    ];
                }
            }
        }

        return $stats;
    }
}