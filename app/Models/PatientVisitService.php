<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class PatientVisitService extends Model
{
    use Auditable;

    protected $fillable = [
        'patient_visit_id',
        'dental_service_id',
        'service_price',
        'quantity',
        'service_notes',
    ];

    protected function casts(): array
    {
        return [
            'service_price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    // Relationships
    public function patientVisit()
    {
        return $this->belongsTo(PatientVisit::class);
    }

    public function dentalService()
    {
        return $this->belongsTo(DentalService::class);
    }

    // Accessor for total price
    public function getTotalPriceAttribute()
    {
        return $this->service_price * $this->quantity;
    }
}