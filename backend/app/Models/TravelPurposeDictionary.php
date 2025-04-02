<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelPurposeDictionary extends Model
{
    /** @use HasFactory<\Database\Factories\TravelPurposeDictionaryFactory> */
    use HasFactory;

    protected $fillable = [
        'travel_purpose',
        'type',
        'note',
        'is_system',
    ];

        /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean'
        ];
    }

    public function locations()
    {
        return $this->belongsToMany(
            Location::class,
            'location_purpose',
            'location_id',
            'travel_purpose_id'
        )->withTimestamps();
    }
}
