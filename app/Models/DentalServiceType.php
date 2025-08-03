<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentalServiceType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    // Relationships
    public function dentalServices()
    {
        return $this->hasMany(DentalService::class);
    }
}