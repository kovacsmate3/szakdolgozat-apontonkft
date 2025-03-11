<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'surveying_instrument',
        'priority',
        'status',
        'description',
    ];

    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function subTasks() {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function parentTask() {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_task', 'task_id', 'user_id')
                    ->withPivot('assigned_at','role_on_task')
                    ->withTimestamps();
    }

    public function journalEntries() {
        return $this->hasMany(JournalEntry::class, 'task_id');
    }
}
