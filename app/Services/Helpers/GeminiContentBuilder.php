<?php

namespace App\Services\Helpers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use App\Models\AuditLog;

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

        return sprintf(
            "User: %s. Full Name: %s %s. Email: %s. Phone: %s. User ID: %s. Role: %s. Branch: %s. Created: %s",
            $user->full_name,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->phone ?? 'Not provided',
            $user->id,
            $roleName,
            $user->branch_name,
            $user->created_at->format('F d, Y')
        );
    }

    /**
     * Build patient content for embedding
     */
    public static function buildPatientContent(Patient $patient): string
    {
        return sprintf(
            "Patient: %s. Phone: %s. Email: %s. Date of Birth: %s. Age: %s. Branch: %s. Address: %s. Registration Date: %s",
            $patient->full_name,
            $patient->phone,
            $patient->email ?? 'Not provided',
            $patient->date_of_birth?->format('F d, Y') ?? 'Not provided',
            $patient->age ?? 'Unknown',
            $patient->registration_branch_name,
            $patient->address ?? 'Not provided',
            $patient->created_at->format('F d, Y')
        );
    }

    /**
     * Build appointment content for embedding
     */
    public static function buildAppointmentContent(Appointment $appointment): string
    {
        return sprintf(
            "Appointment for patient %s scheduled on %s at %s. Status: %s. Queue number: %s. Reason: %s. Branch: %s. Notes: %s",
            $appointment->patient_name,
            $appointment->formatted_date,
            $appointment->formatted_time_range ?? 'Time not set',
            ucfirst($appointment->status->value),
            $appointment->queue_number ?? 'Not assigned',
            $appointment->reason,
            $appointment->branch->name,
            $appointment->notes ?? 'No additional notes'
        );
    }

    /**
     * Build service content for embedding
     */
    public static function buildServiceContent(DentalService $service): string
    {
        return sprintf(
            "Dental Service: %s. Category: %s. Price: ₱%s. %s. Description: Professional dental care service.",
            $service->name,
            $service->service_type_name,
            number_format($service->price, 2),
            $service->is_quantifiable ? 'Quantity-based service' : 'Fixed service'
        );
    }

    /**
     * Build visit content for embedding
     */
    public static function buildVisitContent(PatientVisit $visit): string
    {
        $services = $visit->patientVisitServices->pluck('dentalService.name')->join(', ');

        return sprintf(
            "Patient visit for %s on %s at %s branch. Type: %s. Services: %s. Total amount: ₱%s. Notes: %s",
            $visit->patient_name,
            $visit->visit_date->format('F d, Y g:i A'),
            $visit->branch_name,
            $visit->visit_type,
            $services ?: 'No services recorded',
            number_format($visit->total_amount_paid, 2),
            $visit->notes ?? 'No notes'
        );
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
            $parts[] = "Previous information: " . self::formatValues($auditLog->old_values, $auditLog->auditable_type);
        }

        if ($auditLog->new_values) {
            $parts[] = "Updated information: " . self::formatValues($auditLog->new_values, $auditLog->auditable_type);
        }

        $parts[] = "Date and time: {$auditLog->created_at->format('F d, Y at g:i A')}";

        return implode('. ', $parts) . '.';
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
            try {
                return \Carbon\Carbon::parse($value)->format('F d, Y');
            } catch (\Exception $e) {
                return $value;
            }
        }

        if (str_contains($fieldName, 'time')) {
            try {
                return \Carbon\Carbon::parse($value)->format('g:i A');
            } catch (\Exception $e) {
                return $value;
            }
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
