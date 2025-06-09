<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    /** @use HasFactory<\Database\Factories\FuelPriceFactory> */
    use HasFactory;

    protected $fillable = [
        'period',
        'petrol',
        'mixture',
        'diesel',
        'lp_gas',
    ];

        /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period' => 'date',
            'petrol' => 'float',
            'mixture' => 'float',
            'diesel' => 'float',
            'lp_gas' => 'float',
        ];
    }

}
