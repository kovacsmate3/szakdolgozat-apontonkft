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

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'address_id');
    }

    /**
     * Return a single-line, human‑readable address.
     */
    public function fullAddress(): string
    {
        // e.g. "1151 Budapest, Esthajnal utca utca 3"
        return "{$this->postalcode} {$this->city}, "
            . "{$this->road_name} {$this->public_space_type} "
            . "{$this->building_number}";
    }
}
