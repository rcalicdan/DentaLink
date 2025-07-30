<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentalService extends Model
{
    protected $fillable = [
        'service_name',
        'dental_service_type_id',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
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
