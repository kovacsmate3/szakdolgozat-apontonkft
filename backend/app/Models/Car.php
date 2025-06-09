<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    /** @use HasFactory<\Database\Factories\CarFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_type',
        'license_plate',
        'manufacturer',
        'model',
        'fuel_type',
        'standard_consumption',
        'capacity',
        'fuel_tank_capacity',
    ];

        /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'standard_consumption' => 'float',
            'capacity' => 'integer',
            'fuel_tank_capacity' => 'integer'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fuelExpenses()
    {
        return $this->hasMany(FuelExpense::class, 'car_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'car_id');
    }
}
