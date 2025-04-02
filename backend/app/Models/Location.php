<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'location_type',
        'is_headquarter',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_headquarter' => 'boolean'
        ];
    }

    public function startTrips() {
        return $this->hasMany(Trip::class, 'start_location_id', 'id');
    }

    public function destinationTrips() {
        return $this->hasMany(Trip::class, 'destination_location_id', 'id');
    }

    public function fuelExpenses() {
        return $this->hasMany(FuelExpense::class, 'location_id');
    }

    public function address() {
        return $this->hasOne(Address::class, 'location_id');
    }


    public function travelPurposes() {
        return $this->belongsToMany(TravelPurposeDictionary::class, 'location_purpose',  'location_id', 'travel_purpose_id')
                    ->withTimestamps();
    }

}
