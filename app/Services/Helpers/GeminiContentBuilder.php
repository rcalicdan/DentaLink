<?php

namespace App\Services\Helpers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use App\Models\AuditLog;
use Carbon\Carbon;

class GeminiContentBuilder
{
    /**
     * Build user content for embedding
     */
    public static function buildUserContent(User $user): string
    {
        $roleName = match ($user->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'employee' => 'Employee',
            default => ucfirst(str_replace('_', ' ', $user->role ?? 'Unknown'))
        };

        $phone = $user->phone ?? 'Not provided';
        $createdAt = self::formatDate($user->created_at);

        return <<<TEXT
User: {$user->full_name}. Full Name: {$user->first_name} {$user->last_name}. Email: {$user->email}. Phone: {$phone}. User ID: {$user->id}. Role: {$roleName}. Branch: {$user->branch_name}. Created: {$createdAt}
TEXT;
    }

    /**
     * Build patient content for embedding
     */
    public static function buildPatientContent(Patient $patient): string
    {
        $email = $patient->email ?? 'Not provided';
        $dateOfBirth = $patient->date_of_birth ? self::formatDate($patient->date_of_birth) : 'Not provided';
        $age = $patient->age ?? 'Unknown';
        $address = $patient->address ?? 'Not provided';
        $createdAt = self::formatDate($patient->created_at);

        return <<<TEXT
Patient: {$patient->full_name}. Phone: {$patient->phone}. Email: {$email}. Date of Birth: {$dateOfBirth}. Age: {$age}. Branch: {$patient->registration_branch_name}. Address: {$address}. Registration Date: {$createdAt}
TEXT;
    }

    /**
     * Build appointment content for embedding
     */
    public static function buildAppointmentContent(Appointment $appointment): string
    {
        $formattedDate = $appointment->appointment_date
            ? self::formatDate($appointment->appointment_date)
            : 'Date not set';

        $formattedTime = 'Time not set';
        if ($appointment->start_time && $appointment->end_time) {
            $startTime = self::formatTime($appointment->start_time);
            $endTime = self::formatTime($appointment->end_time);
            $formattedTime = "{$startTime} - {$endTime}";
        } elseif ($appointment->start_time) {
            $formattedTime = self::formatTime($appointment->start_time);
        }

        $status = ucfirst($appointment->status->value);
        $queueNumber = $appointment->queue_number ?? 'Not assigned';
        $branchName = $appointment->branch->name;
        $notes = $appointment->notes ?? 'No additional notes';

        return <<<TEXT
Appointment for patient {$appointment->patient_name} scheduled on {$formattedDate} at {$formattedTime}. Status: {$status}. Queue number: {$queueNumber}. Reason: {$appointment->reason}. Branch: {$branchName}. Notes: {$notes}
TEXT;
    }

    /**
     * Build service content for embedding
     */
    public static function buildServiceContent(DentalService $service): string
    {
        $priceInfo = $service->price
            ? 'Price: ₱' . number_format($service->price, 2)
            : 'Price: Varies depending on patient condition and treatment requirements';

        $serviceType = $service->is_quantifiable
            ? 'Quantity-based service'
            : 'Fixed service';

        $description = $service->description ?? 'Professional dental care service';

        return <<<TEXT
Dental Service: {$service->name}. Category: {$service->service_type_name}. {$priceInfo}. {$serviceType}. Description: {$description}
TEXT;
    }

    /**
     * Build visit content for embedding
     */
    public static function buildVisitContent(PatientVisit $visit): string
    {
        $services = $visit->patientVisitServices->pluck('dentalService.name')->join(', ');
        $servicesText = $services ?: 'No services recorded';
        $formattedVisitDate = self::formatDateTime($visit->visit_date);
        $totalAmount = number_format($visit->total_amount_paid, 2);
        $notes = $visit->notes ?? 'No notes';

        return <<<TEXT
Patient visit for {$visit->patient_name} on {$formattedVisitDate} at {$visit->branch_name} branch. Type: {$visit->visit_type}. Services: {$servicesText}. Total amount: ₱{$totalAmount}. Notes: {$notes}
TEXT;
    }

    /**
     * Build audit log content for embedding
     */
    public static function buildAuditLogContent(AuditLog $auditLog): string
    {
        $parts = [
            "Activity Log:",
            self::formatEvent($auditLog->event),
            self::formatEntityType($auditLog->auditable_type, $auditLog->auditable_id),
        ];

        if ($auditLog->message) {
            $parts[] = "Description: {$auditLog->message}";
        }

        if ($auditLog->user) {
            $parts[] = "Performed by: {$auditLog->user->full_name}";
        }

        if ($auditLog->branch) {
            $parts[] = "Location: {$auditLog->branch->name}";
        }

        if ($auditLog->old_values) {
            $oldValues = self::formatValues($auditLog->old_values, $auditLog->auditable_type);
            $parts[] = "Previous information: {$oldValues}";
        }

        if ($auditLog->new_values) {
            $newValues = self::formatValues($auditLog->new_values, $auditLog->auditable_type);
            $parts[] = "Updated information: {$newValues}";
        }

        $formattedDateTime = self::formatDateTime($auditLog->created_at);
        $parts[] = "Date and time: {$formattedDateTime}";

        return implode('. ', $parts) . '.';
    }

    /**
     * Format date in human-readable format
     * 
     * @param mixed $date Carbon instance, string, or null
     * @return string Formatted date (e.g., "October 29, 2025")
     */
    private static function formatDate($date): string
    {
        if (!$date) {
            return 'Not set';
        }

        try {
            if ($date instanceof Carbon) {
                return $date->format('F j, Y');
            }
            
            $carbonDate = Carbon::parse($date);
            return $carbonDate->format('F j, Y');
        } catch (\Exception $e) {
            logger()->error('Invalid date format: ' . json_encode($date) . ' - ' . $e->getMessage());
            return 'Invalid date';
        }
    }

    /**
     * Format time in human-readable format
     * 
     * @param mixed $time Carbon instance, string, or null
     * @return string Formatted time (e.g., "2:30 PM")
     */
    private static function formatTime($time): string
    {
        if (!$time) {
            return 'Not set';
        }

        try {
            if ($time instanceof Carbon) {
                return $time->format('g:i A');
            }
            
            $carbonTime = Carbon::parse($time);
            return $carbonTime->format('g:i A');
        } catch (\Exception $e) {
            logger()->error('Invalid time format: ' . json_encode($time) . ' - ' . $e->getMessage());
            return 'Invalid time';
        }
    }

    /**
     * Format date and time together in human-readable format
     * 
     * @param mixed $datetime Carbon instance, string, or null
     * @return string Formatted datetime (e.g., "October 29, 2025 at 2:30 PM")
     */
    private static function formatDateTime($datetime): string
    {
        if (!$datetime) {
            return 'Not set';
        }

        try {
            if ($datetime instanceof Carbon) {
                return $datetime->format('F j, Y \a\t g:i A');
            }
            
            $carbonDatetime = Carbon::parse($datetime);
            return $carbonDatetime->format('F j, Y \a\t g:i A');
        } catch (\Exception $e) {
            logger()->error('Invalid datetime format: ' . json_encode($datetime) . ' - ' . $e->getMessage());
            return 'Invalid date/time';
        }
    }

    /**
     * Format event type into user-friendly language
     */
    private static function formatEvent(string $event): string
    {
        return match (strtolower($event)) {
            'created' => 'Action: New record created',
            'updated' => 'Action: Record updated',
            'deleted' => 'Action: Record deleted',
            'restored' => 'Action: Record restored',
            'viewed' => 'Action: Record viewed',
            'logged_in' => 'Action: User logged in',
            'logged_out' => 'Action: User logged out',
            'failed_login' => 'Action: Failed login attempt',
            'exported' => 'Action: Data exported',
            'imported' => 'Action: Data imported',
            'status_changed' => 'Action: Status changed',
            'queue_updated' => 'Action: Queue number updated',
            'appointment_completed' => 'Action: Appointment completed',
            'appointment_cancelled' => 'Action: Appointment cancelled',
            'visit_recorded' => 'Action: Visit recorded',
            'service_added' => 'Action: Service added',
            'payment_received' => 'Action: Payment received',
            'stock_updated' => 'Action: Stock updated',
            default => "Action: " . ucfirst(str_replace('_', ' ', $event))
        };
    }

    /**
     * Format entity type into user-friendly names
     */
    private static function formatEntityType(string $entityType, $entityId): string
    {
        $readableType = match ($entityType) {
            'App\\Models\\User' => 'User account',
            'App\\Models\\Patient' => 'Patient record',
            'App\\Models\\Appointment' => 'Appointment',
            'App\\Models\\PatientVisit' => 'Patient visit',
            'App\\Models\\PatientVisitService' => 'Service provided during visit',
            'App\\Models\\DentalService' => 'Dental service',
            'App\\Models\\DentalServiceType' => 'Service category',
            'App\\Models\\Branch' => 'Branch office',
            'App\\Models\\Inventory' => 'Inventory item',
            'App\\Models\\AuditLog' => 'Activity log',
            default => self::extractModelName($entityType)
        };

        return "Record type: {$readableType} (ID: {$entityId})";
    }

    /**
     * Extract readable model name from full class path
     */
    private static function extractModelName(string $entityType): string
    {
        $className = class_basename($entityType);
        $readable = preg_replace('/(?<!^)[A-Z]/', ' $0', $className);
        return ucfirst(strtolower($readable));
    }

    /**
     * Format old/new values into readable format
     */
    private static function formatValues(array $values, string $entityType): string
    {
        if (empty($values)) {
            return 'No data';
        }

        $formatted = [];

        foreach ($values as $key => $value) {
            if (in_array($key, ['id', 'created_at', 'updated_at', 'remember_token', 'password'])) {
                continue;
            }

            $readableKey = self::formatFieldName($key);
            $readableValue = self::formatFieldValue($key, $value);

            $formatted[] = "{$readableKey}: {$readableValue}";
        }

        return empty($formatted) ? 'No changes' : implode(', ', $formatted);
    }

    /**
     * Format field names into user-friendly labels
     */
    private static function formatFieldName(string $fieldName): string
    {
        $fieldMap = [
            'id' => 'ID',
            'created_at' => 'Created date',
            'updated_at' => 'Last updated',
            'deleted_at' => 'Deleted date',
            'user_id' => 'User',
            'patient_id' => 'Patient',
            'branch_id' => 'Branch',
            'appointment_id' => 'Appointment',
            'dental_service_id' => 'Service',
            'dental_service_type_id' => 'Service category',
            'patient_visit_id' => 'Visit',
            'created_by' => 'Created by',
            'full_name' => 'Full name',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email address',
            'phone' => 'Phone number',
            'date_of_birth' => 'Date of birth',
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'start_time' => 'Start time',
            'end_time' => 'End time',
            'queue_number' => 'Queue number',
            'visit_date' => 'Visit date',
            'total_amount' => 'Total amount',
            'total_amount_paid' => 'Total amount paid',
            'amount_paid' => 'Amount paid',
            'service_price' => 'Service price',
            'is_active' => 'Status',
            'is_quantifiable' => 'Quantity-based',
            'has_visit' => 'Has visit',
            'current_stock' => 'Current stock',
            'minimum_stock' => 'Minimum stock',
            'registration_branch_id' => 'Registration branch',
            'service_notes' => 'Service notes',
            'reason' => 'Reason for visit',
            'notes' => 'Notes',
            'address' => 'Address',
            'role' => 'Role',
            'status' => 'Status',
            'category' => 'Category',
            'quantity' => 'Quantity',
        ];

        if (isset($fieldMap[$fieldName])) {
            return $fieldMap[$fieldName];
        }

        return ucwords(str_replace('_', ' ', $fieldName));
    }

    /**
     * Format field values into user-friendly format
     */
    private static function formatFieldValue(string $fieldName, $value): string
    {
        if (is_null($value)) {
            return 'Not set';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($fieldName === 'role') {
            return match ($value) {
                'super_admin' => 'Super Admin',
                'admin' => 'Admin',
                'employee' => 'Employee',
                default => ucfirst(str_replace('_', ' ', $value))
            };
        }

        if ($fieldName === 'status') {
            return match ($value) {
                'waiting' => 'Waiting',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'no_show' => 'No Show',
                'rescheduled' => 'Rescheduled',
                default => ucfirst(str_replace('_', ' ', $value))
            };
        }

        if ($fieldName === 'category') {
            return ucfirst(str_replace('_', ' ', $value));
        }

        if (str_starts_with($fieldName, 'has_') && is_numeric($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (str_starts_with($fieldName, 'is_') && is_numeric($value)) {
            return $value ? 'Active' : 'Inactive';
        }

        if (str_contains($fieldName, 'date') || str_contains($fieldName, '_at')) {
            return self::formatDate($value);
        }

        if (str_contains($fieldName, 'time') && !str_contains($fieldName, 'date')) {
            return self::formatTime($value);
        }

        if (str_contains($fieldName, 'amount') || str_contains($fieldName, 'price') || str_contains($fieldName, 'cost')) {
            return '₱' . number_format((float) $value, 2);
        }

        if (in_array($fieldName, ['quantity', 'current_stock', 'minimum_stock', 'queue_number'])) {
            return number_format((int) $value);
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn($v) => is_string($v) ? $v : json_encode($v), $value));
        }

        if (is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}