<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelExpense extends Model
{
    /** @use HasFactory<\Database\Factories\FuelExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'location_id',
        'expense_date',
        'amount',
        'currency',
        'fuel_quantity',
        'odometer',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expense_date' => 'datetime',
            'amount' => 'float',
            'fuel_quantity' => 'float',
            'odometer' => 'integer',
        ];
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
