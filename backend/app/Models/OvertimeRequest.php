<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    /** @use HasFactory<\Database\Factories\OvertimeRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'hours',
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
            'date' => 'date',
            'hours' => 'datetime:H:i:s',
            'processed_at' => 'datetime',
        ];
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /*
    public function journalEntry() {
        return $this->hasOne(JournalEntry::class, 'overtimerequest_id');
    }
    */

}
