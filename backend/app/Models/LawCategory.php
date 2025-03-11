<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawCategory extends Model
{
    /** @use HasFactory<\Database\Factories\LawCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function laws()
    {
        return $this->hasMany(Law::class, 'category_id');
    }
}
