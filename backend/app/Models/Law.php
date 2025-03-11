<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Law extends Model
{
    /** @use HasFactory<\Database\Factories\LawFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'official_ref',
        'date_of_enactment',
        'is_active',
        'link',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_enactment' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(LawCategory::class, 'category_id');
    }
}
