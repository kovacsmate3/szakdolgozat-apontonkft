<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'job_number',
        'project_name',
        'location',
        'parcel_identification_number',
        'deadline',
        'description',
        'status',
        'address_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime'
        ];
    }

    public function tasks() {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function address() {
        return $this->belongsTo(Address::class, 'address_id');
    }

}
