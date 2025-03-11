<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'module',
        'description',
    ];

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id')->withPivot(['is_active', 'revoked_at'])->withTimestamps();
    }
}
