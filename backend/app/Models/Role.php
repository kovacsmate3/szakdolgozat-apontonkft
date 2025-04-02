<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'description',
    ];

    public function users() {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id','permission_id')->withPivot(['is_active', 'revoked_at'])->withTimestamps();
    }

}
