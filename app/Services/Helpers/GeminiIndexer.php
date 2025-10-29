<?php

namespace App\Services\Helpers;

use App\Models\KnowledgeBase;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DentalService;
use App\Models\DentalServiceType;
use App\Models\Inventory;
use App\Models\PatientVisit;
use App\Models\PatientVisitService;

class GeminiIndexer
{
    private const BATCH_SIZE = 50;
    private const RATE_LIMIT_DELAY = 100000; // microseconds

    /**
     * Index a user
     */
    public static function indexUser(User $user, array $embedding): void
    {
        $content = GeminiContentBuilder::buildUserContent($user);

        KnowledgeBase::storeEmbedding(
            entityType: 'user',
            entityId: $user->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'user_id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'branch_name' => $user->branch_name,
                'updated_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Index a patient record
     */
    public static function indexPatient(Patient $patient, array $embedding): void
    {
        $content = GeminiContentBuilder::buildPatientContent($patient);

        KnowledgeBase::storeEmbedding(
            entityType: 'patient',
            entityId: $patient->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'patient_id' => $patient->id,
                'branch_id' => $patient->registration_branch_id,
                'full_name' => $patient->full_name,
                'updated_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Index an appointment
     */
    public static function indexAppointment(Appointment $appointment, array $embedding): void
    {
        $content = GeminiContentBuilder::buildAppointmentContent($appointment);

        KnowledgeBase::storeEmbedding(
            entityType: 'appointment',
            entityId: $appointment->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'branch_id' => $appointment->branch_id,
                'appointment_date' => $appointment->appointment_date->toISOString(),
                'status' => $appointment->status->value,
            ]
        );
    }

    /**
     * Index a dental service
     */
    public static function indexDentalService(DentalService $service, array $embedding): void
    {
        $content = GeminiContentBuilder::buildServiceContent($service);

        KnowledgeBase::storeEmbedding(
            entityType: 'dental_service',
            entityId: $service->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'service_id' => $service->id,
                'service_type_id' => $service->dental_service_type_id,
                'price' => (float) $service->price,
                'is_quantifiable' => $service->is_quantifiable,
            ]
        );
    }

    /**
     * Index a patient visit
     */
    public static function indexPatientVisit(PatientVisit $visit, array $embedding): void
    {
        $content = GeminiContentBuilder::buildVisitContent($visit);

        KnowledgeBase::storeEmbedding(
            entityType: 'patient_visit',
            entityId: $visit->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'branch_id' => $visit->branch_id,
                'visit_date' => $visit->visit_date->toISOString(),
                'total_amount' => (float) $visit->total_amount_paid,
            ]
        );
    }

    /**
     * Index a branch
     */
    public static function indexBranch(Branch $branch, array $embedding): void
    {
        $content = "Branch: {$branch->name}, Address: {$branch->address}, Phone: {$branch->phone}, Email: {$branch->email}";

        KnowledgeBase::storeEmbedding(
            'branch',
            $branch->id,
            $content,
            $embedding,
            [
                'name' => $branch->name,
                'address' => $branch->address,
            ]
        );
    }

    /**
     * Index a dental service type
     */
    public static function indexDentalServiceType(DentalServiceType $dentalServiceType, array $embedding): void
    {
        $content = "Dental Service Type: {$dentalServiceType->name}, Description: {$dentalServiceType->description}";

        KnowledgeBase::storeEmbedding(
            'dental_service_type',
            $dentalServiceType->id,
            $content,
            $embedding,
            [
                'name' => $dentalServiceType->name,
            ]
        );
    }

    /**
     * Index an inventory item
     */
    public static function indexInventory(Inventory $inventory, array $embedding): void
    {
        $content = "Inventory Item: {$inventory->name}, Category: {$inventory->category}, Branch: {$inventory->branch_name}, Current Stock: {$inventory->current_stock}, Minimum Stock: {$inventory->minimum_stock}, Status: {$inventory->stock_status}";

        KnowledgeBase::storeEmbedding(
            'inventory',
            $inventory->id,
            $content,
            $embedding,
            [
                'name' => $inventory->name,
                'category' => $inventory->category,
                'branch_id' => $inventory->branch_id,
                'is_low_stock' => $inventory->is_low_stock,
            ]
        );
    }

    /**
     * Index a patient visit service
     */
    public static function indexPatientVisitService(PatientVisitService $patientVisitService, array $embedding): void
    {
        $patient = $patientVisitService->patientVisit->patient;
        $service = $patientVisitService->dentalService;

        $content = "Patient Visit Service: Patient {$patient->full_name} received {$service->name} (Type: {$service->service_type_name}), Quantity: {$patientVisitService->quantity}, Price: {$patientVisitService->service_price}, Total: {$patientVisitService->total_price}, Notes: {$patientVisitService->service_notes}";

        KnowledgeBase::storeEmbedding(
            'patient_visit_service',
            $patientVisitService->id,
            $content,
            $embedding,
            [
                'patient_id' => $patient->id,
                'dental_service_id' => $service->id,
                'patient_visit_id' => $patientVisitService->patient_visit_id,
            ]
        );
    }

    /**
     * Index an audit log
     */
    public static function indexAuditLog(AuditLog $auditLog, array $embedding): void
    {
        $content = self::buildAuditLogContent($auditLog);

        KnowledgeBase::storeEmbedding(
            entityType: 'audit_log',
            entityId: $auditLog->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'audit_log_id' => $auditLog->id,
                'auditable_type' => $auditLog->auditable_type,
                'auditable_id' => $auditLog->auditable_id,
                'event' => $auditLog->event,
                'user_id' => $auditLog->user_id,
                'branch_id' => $auditLog->branch_id,
                'ip_address' => $auditLog->ip_address,
                'created_at' => $auditLog->created_at->toISOString(),
            ]
        );
    }

    /**
     * Build content string for audit log
     */
    private static function buildAuditLogContent(AuditLog $auditLog): string
    {
        $parts = [
            "Audit Log Entry:",
            "Event: {$auditLog->event}",
            "Entity: {$auditLog->auditable_type} (ID: {$auditLog->auditable_id})",
        ];

        if ($auditLog->message) {
            $parts[] = "Message: {$auditLog->message}";
        }

        if ($auditLog->user) {
            $parts[] = "User: {$auditLog->user->full_name} (ID: {$auditLog->user_id})";
        }

        if ($auditLog->branch) {
            $parts[] = "Branch: {$auditLog->branch->name} (ID: {$auditLog->branch_id})";
        }

        if ($auditLog->old_values) {
            $parts[] = "Old Values: " . json_encode($auditLog->old_values);
        }

        if ($auditLog->new_values) {
            $parts[] = "New Values: " . json_encode($auditLog->new_values);
        }

        $parts[] = "Date: {$auditLog->created_at->format('Y-m-d H:i:s')}";

        return implode(', ', $parts);
    }

    /**
     * Get content for indexing (without generating embedding)
     */
    public static function getContentForUser(User $user): string
    {
        return GeminiContentBuilder::buildUserContent($user);
    }

    public static function getContentForPatient(Patient $patient): string
    {
        return GeminiContentBuilder::buildPatientContent($patient);
    }

    public static function getContentForAppointment(Appointment $appointment): string
    {
        return GeminiContentBuilder::buildAppointmentContent($appointment);
    }

    public static function getContentForDentalService(DentalService $service): string
    {
        return GeminiContentBuilder::buildServiceContent($service);
    }

    public static function getContentForPatientVisit(PatientVisit $visit): string
    {
        return GeminiContentBuilder::buildVisitContent($visit);
    }

    public static function getContentForBranch(Branch $branch): string
    {
        return "Branch: {$branch->name}, Address: {$branch->address}, Phone: {$branch->phone}, Email: {$branch->email}";
    }

    public static function getContentForDentalServiceType(DentalServiceType $dentalServiceType): string
    {
        return "Dental Service Type: {$dentalServiceType->name}, Description: {$dentalServiceType->description}";
    }

    public static function getContentForInventory(Inventory $inventory): string
    {
        return "Inventory Item: {$inventory->name}, Category: {$inventory->category}, Branch: {$inventory->branch_name}, Current Stock: {$inventory->current_stock}, Minimum Stock: {$inventory->minimum_stock}, Status: {$inventory->stock_status}";
    }

    public static function getContentForPatientVisitService(PatientVisitService $patientVisitService): string
    {
        $patient = $patientVisitService->patientVisit->patient;
        $service = $patientVisitService->dentalService;

        return "Patient Visit Service: Patient {$patient->full_name} received {$service->name} (Type: {$service->service_type_name}), Quantity: {$patientVisitService->quantity}, Price: {$patientVisitService->service_price}, Total: {$patientVisitService->total_price}, Notes: {$patientVisitService->service_notes}";
    }

    public static function getContentForAuditLog(AuditLog $auditLog): string
    {
        return self::buildAuditLogContent($auditLog);
    }
}