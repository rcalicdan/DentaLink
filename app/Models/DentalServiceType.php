<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class DentalServiceType extends Model
{
    use Auditable;
    
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