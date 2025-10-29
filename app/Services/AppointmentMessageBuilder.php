<?php

namespace App\Services;

use App\Models\Appointment;

class AppointmentMessageBuilder
{
    /**
     * Build SMS message for appointment
     */
    public function buildSmsMessage(Appointment $appointment): string
    {
        $patientName = $appointment->patient->first_name;
        $date = $appointment->appointment_date->format('M d, Y');
        $branch = $appointment->branch->name ?? 'our clinic';
        $timeInfo = $this->getTimeInfo($appointment);

        $timeText = $timeInfo ? " at {$timeInfo}" : '';
        $queueText = !empty($appointment->queue_number)
            ? " Your queue number is #{$appointment->queue_number}."
            : '';

        return <<<SMS
Hi {$patientName}!

This is a reminder that you have a dental appointment today, {$date}{$timeText} at {$branch}.
{$queueText}
See you soon!
SMS;
    }

    /**
     * Build email data for appointment
     */
    public function buildEmailData(Appointment $appointment): array
    {
        $patient = $appointment->patient;
        $date = $appointment->appointment_date->format('F d, Y');
        $branch = $appointment->branch->name ?? 'Nice Smile Clinic';
        $timeInfo = $this->getTimeInfo($appointment);
        $queueNumber = $appointment->queue_number;

        return [
            'subject' => $this->buildEmailSubject(),
            'text' => $this->buildEmailText($patient, $date, $timeInfo, $branch, $queueNumber),
            'html' => $this->buildEmailHtml($patient, $date, $timeInfo, $branch, $queueNumber)
        ];
    }

    /**
     * Get formatted time information from appointment
     */
    private function getTimeInfo(Appointment $appointment): ?string
    {
        if (!empty($appointment->formatted_time_range)) {
            return $appointment->formatted_time_range;
        }

        if ($appointment->start_time && $appointment->end_time) {
            return $appointment->start_time->format('g:i A') . ' - ' . $appointment->end_time->format('g:i A');
        }

        if ($appointment->start_time) {
            return $appointment->start_time->format('g:i A');
        }

        return null;
    }

    private function buildEmailSubject(): string
    {
        return "Reminder: Your Dental Appointment Today";
    }

    private function buildEmailText($patient, string $date, ?string $timeInfo, string $branch, $queueNumber): string
    {
        $text = "Hi {$patient->first_name}!\n\n";
        $text .= "This is a friendly reminder that you have a dental appointment today, {$date}";

        if ($timeInfo) {
            $text .= " at {$timeInfo}";
        }

        $text .= " at {$branch}.\n\n";

        if ($queueNumber) {
            $text .= "Your queue number is #{$queueNumber}.\n\n";
        }

        $text .= "Please arrive 10-15 minutes early to complete any necessary paperwork.\n\n";
        $text .= "If you need to reschedule or cancel, please contact us as soon as possible.\n\n";
        $text .= "We look forward to seeing you!\n\n";
        $text .= "Best regards,\n";
        $text .= "Nice Smile Clinic Team";

        return $text;
    }

    private function buildEmailHtml($patient, string $date, ?string $timeInfo, string $branch, $queueNumber): string
    {
        $queueHtml = $queueNumber ? $this->buildQueueHtml($queueNumber) : '';
        $timeHtml = $timeInfo ? $this->buildTimeHtml($timeInfo) : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reminder</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                🦷 Appointment Reminder
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333333; line-height: 1.6;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #333;">
                                Hi <strong style="color: #4CAF50;">{$patient->first_name}</strong>,
                            </p>
                            
                            <p style="margin: 0 0 30px 0; font-size: 16px; color: #555;">
                                This is a friendly reminder that you have a dental appointment today:
                            </p>
                            
                            <!-- Appointment Details Card -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f9f9f9; border-left: 4px solid #4CAF50; border-radius: 4px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <p style="margin: 10px 0; color: #333; font-size: 16px;">
                                            <strong style="color: #555;">Date:</strong> {$date}
                                        </p>
                                        {$timeHtml}
                                        <p style="margin: 10px 0; color: #333; font-size: 16px;">
                                            <strong style="color: #555;">Location:</strong> {$branch}
                                        </p>
                                        {$queueHtml}
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Important Information Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #fff3cd; border-radius: 4px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px; border-left: 4px solid #ffc107;">
                                        <p style="margin: 0; font-size: 15px; color: #856404;">
                                            <strong>📌 Important:</strong> Please arrive 10-15 minutes early to complete any necessary paperwork.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 25px 0 20px 0; font-size: 16px; color: #555; line-height: 1.6;">
                                If you need to reschedule or cancel, please contact us as soon as possible.
                            </p>
                            
                            <p style="margin: 25px 0 10px 0; font-size: 16px; color: #333;">
                                We look forward to seeing you! 😊
                            </p>
                            
                            <p style="margin: 20px 0 0 0; font-size: 16px; color: #555;">
                                Best regards,<br>
                                <strong style="color: #4CAF50;">Nice Smile Clinic Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #888; line-height: 1.5;">
                                This is an automated reminder. Please do not reply to this email.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #aaa;">
                                © 2025 Nice Smile Clinic. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function buildQueueHtml($queueNumber): string
    {
        return <<<HTML
                        <p style="margin: 10px 0; color: #333; font-size: 16px;">
                            <strong style="color: #555;">Queue Number:</strong> 
                            <span style="font-size: 28px; font-weight: bold; color: #4CAF50;">#{$queueNumber}</span>
                        </p>
HTML;
    }

    private function buildTimeHtml(string $timeInfo): string
    {
        return <<<HTML
                        <p style="margin: 10px 0; color: #333; font-size: 16px;">
                            <strong style="color: #555;">Time:</strong> {$timeInfo}
                        </p>
HTML;
    }
}
