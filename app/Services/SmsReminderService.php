<?php

namespace App\Services;

use App\Models\Appointment;
use Hibla\HttpClient\Http;
use Illuminate\Support\Facades\Log;

use function Hibla\async;
use function Hibla\await;

class SmsReminderService
{
    private const MAX_RETRIES = 3;
    private const BASE_DELAY = 1.0;
    private const BACKOFF_MULTIPLIER = 2.0;

    public function __construct(
        private AppointmentMessageBuilder $messageBuilder
    ) {}

    /**
     * Send SMS reminder for an appointment
     */
    public function send(Appointment $appointment)
    {
        return async(function () use ($appointment) {
            $patient = $appointment->patient;
            $phone = $this->formatPhoneNumber($patient->phone);

            try {
                $this->validateConfig();
                
                $message = $this->messageBuilder->buildSmsMessage($appointment);
                
                $response = await($this->sendToApi($phone, $message));
                $body = $response->json();

                if (isset($body[0]['status']) && $body[0]['status'] === 'Queued') {
                    $this->logSuccess($appointment, $patient, $phone);

                    return [
                        'success' => true,
                        'patient' => $patient->full_name,
                        'recipient' => $phone,
                        'response' => $body
                    ];
                }

                $this->logUnexpectedStatus($appointment, $patient, $phone, $body);

                return [
                    'success' => false,
                    'patient' => $patient->full_name,
                    'recipient' => $phone,
                    'reason' => 'Unexpected status',
                    'response' => $body
                ];
            } catch (\Throwable $e) {
                $this->logError($appointment, $patient, $phone, $e);

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
     * Validate API configuration
     */
    private function validateConfig(): void
    {
        if (empty(env('SEMAPHORE_API_KEY'))) {
            throw new \Exception('Semaphore API key is not configured.');
        }
    }

    /**
     * Send SMS via Semaphore API
     */
    private function sendToApi(string $phone, string $message)
    {
        $apiKey = env('SEMAPHORE_API_KEY');
        $senderName = env('SEMAPHORE_SENDER_NAME', 'DENTALINK');

        return Http::asForm()
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
            ]);
    }

    /**
     * Format phone number to Philippine mobile format
     */
    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10 && strpos($phone, '9') === 0) {
            return '0' . $phone;
        }

        if (strlen($phone) === 12 && strpos($phone, '639') === 0) {
            return '0' . substr($phone, 2);
        }

        return $phone;
    }

    private function logSuccess($appointment, $patient, $phone): void
    {
        Log::info('Appointment SMS reminder sent', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'phone' => $phone
        ]);
    }

    private function logUnexpectedStatus($appointment, $patient, $phone, $body): void
    {
        Log::warning('Appointment SMS reminder unexpected status', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'phone' => $phone,
            'response' => $body
        ]);
    }

    private function logError($appointment, $patient, $phone, \Throwable $e): void
    {
        Log::error('Appointment SMS reminder failed', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'phone' => $phone,
            'error' => $e->getMessage()
        ]);
    }
}