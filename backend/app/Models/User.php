<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'firstname',
        'lastname',
        'birthdate',
        'phonenumber',
        'email',
        'email_verified_at',
        'password',
        'password_changed_at',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'password_changed_at' => 'datetime'
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function cars()
    {
        return $this->hasMany(Car::class, 'user_id');
    }

    public function fuelExpenses()
    {
        return $this->hasMany(FuelExpense::class, 'user_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'user_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class, 'user_id');
    }

    public function processedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'processed_by');
    }

    public function processedOvertimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class, 'processed_by');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'user_id');
    }
}
