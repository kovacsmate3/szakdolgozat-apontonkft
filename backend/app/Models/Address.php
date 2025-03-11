<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'location_id',
        'country',
        'postalcode',
        'city',
        'road_name',
        'public_space_type',
        'building_number'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'postalcode' => 'integer'
        ];
    }

    public function location() {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
