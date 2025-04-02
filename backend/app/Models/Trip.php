<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    /** @use HasFactory<\Database\Factories\TripFactory> */
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'start_location_id',
        'destination_location_id',
        'start_time',
        'end_time',
        'planned_distance',
        'actual_distance',
        'start_odometer',
        'end_odometer',
        'planned_duration',
        'actual_duration',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'planned_distance' => 'float',
            'actual_distance' => 'float',
            'start_odometer' => 'integer',
            'end_odometer' => 'integer',
            'planned_duration' => 'datetime:H:i:s',
            'actual_duration' => 'datetime:H:i:s'
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

    public function startLocation()
    {
        return $this->belongsTo(Location::class, 'start_location_id');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }
}
