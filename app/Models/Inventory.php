<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'branch_id',
        'name',
        'category',
        'current_stock',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getBranchNameAttribute(): string
    {
        return $this->branch->name;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function getStockStatusAttribute(): string
    {
        return $this->is_low_stock ? 'Low' : 'Normal';
    }
}
