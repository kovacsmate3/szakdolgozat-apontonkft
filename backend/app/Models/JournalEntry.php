<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    /** @use HasFactory<\Database\Factories\JournalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'task_id',
        'work_date',
        'hours',
        'note',
        'work_type',
        'leaverequest_id',
        'overtimerequest_id',
    ];

        /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'hours' => 'datetime:H:i:s',
        ];
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function task() {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function leaveRequest() {
        return $this->belongsTo(LeaveRequest::class, 'leaverequest_id');
    }

    public function overtimeRequest() {
        return $this->belongsTo(OvertimeRequest::class, 'overtimerequest_id');
    }
}
