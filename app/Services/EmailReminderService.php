<?php

namespace App\Services;

use App\Models\Appointment;
use Hibla\HttpClient\Http;
use Illuminate\Support\Facades\Log;

use function Hibla\async;
use function Hibla\await;

class EmailReminderService
{
    private const MAX_RETRIES = 3;
    private const BASE_DELAY = 1.0;
    private const BACKOFF_MULTIPLIER = 2.0;

    public function __construct(
        private AppointmentMessageBuilder $messageBuilder
    ) {}

    /**
     * Send email reminder for an appointment
     */
    public function send(Appointment $appointment)
    {
        return async(function () use ($appointment) {
            $patient = $appointment->patient;
            $email = $patient->email;

            try {
                $this->validateConfig();
                
                $emailData = $this->messageBuilder->buildEmailData($appointment);
                
                $response = await($this->sendToApi($patient, $email, $emailData));

                if ($response->successful()) {
                    $this->logSuccess($appointment, $patient, $email, $response);

                    return [
                        'success' => true,
                        'patient' => $patient->full_name,
                        'recipient' => $email,
                        'response' => $response->json()
                    ];
                }

                $this->logUnexpectedStatus($appointment, $patient, $email, $response);

                return [
                    'success' => false,
                    'patient' => $patient->full_name,
                    'recipient' => $email,
                    'reason' => "HTTP {$response->status()}",
                    'response' => $response->body()
                ];
            } catch (\Throwable $e) {
                $this->logError($appointment, $patient, $email, $e);

                return [
                    'success' => false,
                    'patient' => $patient->full_name,
                    'recipient' => $email,
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Validate API configuration
     */
    private function validateConfig(): void
    {
        if (empty(env('MAILTRAP_API_TOKEN'))) {
            throw new \Exception('Mailtrap API token is not configured.');
        }
    }

    /**
     * Send email via Mailtrap API
     */
    private function sendToApi($patient, string $email, array $emailData)
    {
        $apiToken = env('MAILTRAP_API_TOKEN');
        $apiUrl = env('MAILTRAP_API_URL', 'https://send.api.mailtrap.io/api/send');
        $fromEmail = env('MAIL_FROM_ADDRESS', 'hello@nicesmileclinic.com');
        $fromName = env('MAIL_FROM_NAME', 'Nice Smile Clinic');

        return Http::asJson()
            ->withToken($apiToken, 'Bearer')
            ->retry(
                maxRetries: self::MAX_RETRIES,
                baseDelay: self::BASE_DELAY,
                backoffMultiplier: self::BACKOFF_MULTIPLIER
            )
            ->timeout(30)
            ->post($apiUrl, [
                'from' => [
                    'email' => $fromEmail,
                    'name' => $fromName
                ],
                'to' => [
                    [
                        'email' => $email,
                        'name' => $patient->full_name
                    ]
                ],
                'subject' => $emailData['subject'],
                'text' => $emailData['text'],
                'html' => $emailData['html'],
                'category' => 'Appointment Reminder'
            ]);
    }

    private function logSuccess($appointment, $patient, $email, $response): void
    {
        Log::info('Appointment email reminder sent', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'email' => $email,
            'response' => $response->json()
        ]);
    }

    private function logUnexpectedStatus($appointment, $patient, $email, $response): void
    {
        Log::warning('Appointment email reminder unexpected status', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'email' => $email,
            'status' => $response->status(),
            'response' => $response->body()
        ]);
    }

    private function logError($appointment, $patient, $email, \Throwable $e): void
    {
        Log::error('Appointment email reminder failed', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'email' => $email,
            'error' => $e->getMessage()
        ]);
    }
}