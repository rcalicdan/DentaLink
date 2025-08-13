<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class DentalService extends Model
{
    use Auditable;
    
    protected $fillable = [
        'name',
        'dental_service_type_id',
        'price',
        'is_quantifiable', 
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_quantifiable' => 'boolean', 
        ];
    }

    public function getServiceTypeNameAttribute()
    {
        return $this->dentalServiceType?->name ?? 'N/A';
    }

    // Relationships
    public function dentalServiceType()
    {
        return $this->belongsTo(DentalServiceType::class);
    }

    public function patientVisitServices()
    {
        return $this->hasMany(PatientVisitService::class);
    }
}