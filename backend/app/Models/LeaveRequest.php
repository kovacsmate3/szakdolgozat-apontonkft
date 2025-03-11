<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'status',
        'reason',
        'processed_at',
        'processed_by',
        'decision_comment'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'processed_at' => 'datetime'
        ];
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function journalEntries() {
        return $this->hasMany(JournalEntry::class, 'leaverequest_id');
    }
    
}
