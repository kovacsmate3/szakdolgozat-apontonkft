<?php

namespace App\Http\Controllers\Api\WorkLog;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JournalEntry::query();

        // User filter
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Task filter
        if ($request->has('task_id')) {
            $query->where('task_id', $request->input('task_id'));
        }

        // Work type filter
        if ($request->has('work_type')) {
            $query->where('work_type', $request->input('work_type'));
        }

        // Date range filters
        if ($request->has('date_from')) {
            $query->where('work_date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('work_date', '<=', $request->input('date_to'));
        }

        // Month filter (useful for monthly reports)
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('work_date', $request->input('month'))
                ->whereYear('work_date', $request->input('year'));
        } elseif ($request->has('month') && !$request->has('year')) {
            $query->whereMonth('work_date', $request->input('month'))
                ->whereYear('work_date', date('Y'));
        }

        // Leave request filter
        if ($request->has('leaverequest_id')) {
            $query->where('leaverequest_id', $request->input('leaverequest_id'));
        }

        // Overtime request filter
        if ($request->has('overtimerequest_id')) {
            $query->where('overtimerequest_id', $request->input('overtimerequest_id'));
        }

        // Include relationships
        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['user', 'task', 'leaveRequest', 'overtimeRequest'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        // Sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'work_date');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('work_date', 'desc')->orderBy('created_at', 'desc');
        }

        // Pagination or get all
        if ($request->has('per_page')) {
            $journalEntries = $query->paginate($request->input('per_page', 15));
        } else {
            $journalEntries = $query->get();
        }

        return response()->json($journalEntries, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');

        $rules = [
            'work_date' => ['required', 'date'],
            'hours' => ['required', 'date_format:H:i:s'],
            'note' => ['nullable', 'string'],
            'work_type' => ['required', 'string', 'max:50'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'leaverequest_id' => ['nullable', 'exists:leave_requests,id'],
            'overtimerequest_id' => ['nullable', 'exists:overtime_requests,id'],
        ];

        // Admin can set user_id, regular users cannot
        if ($isAdmin) {
            $rules['user_id'] = ['required', 'exists:users,id'];
        }

        $messages = [
            'work_date.required' => 'A munkanap dátumának megadása kötelező.',
            'work_date.date' => 'A munkanap dátuma érvénytelen formátumú.',

            'hours.required' => 'A munkaórák megadása kötelező.',
            'hours.date_format' => 'A munkaórák formátuma érvénytelen. A helyes formátum: ÓÓ:PP:MM.',

            'work_type.required' => 'A munka típusának megadása kötelező.',
            'work_type.string' => 'A munka típusa csak szöveg formátumú lehet.',
            'work_type.max' => 'A munka típusa maximum 50 karakter hosszú lehet.',

            'note.string' => 'A megjegyzés csak szöveg formátumú lehet.',

            'task_id.exists' => 'A megadott feladat nem létezik.',

            'leaverequest_id.exists' => 'A megadott szabadságkérelem nem létezik.',

            'overtimerequest_id.exists' => 'A megadott túlórakérelem nem létezik.',

            'user_id.required' => 'A felhasználó azonosítójának megadása kötelező.',
            'user_id.exists' => 'A megadott felhasználó nem létezik.',
        ];

        $validated = $request->validate($rules, $messages);

        // Logical validations
        if (!empty($validated['leaverequest_id']) && !empty($validated['overtimerequest_id'])) {
            return response()->json([
                'message' => 'Egy naplóbejegyzés nem kapcsolódhat egyszerre szabadságkérelemhez és túlórakérelemhez is.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // For non-admin users, set the current user's ID
        if (!$isAdmin) {
            $validated['user_id'] = $currentUserId;
        }

        // Create the journal entry
        $journalEntry = JournalEntry::create($validated);

        // Load relationships for response
        $journalEntry->load(['user', 'task', 'leaveRequest', 'overtimeRequest']);

        return response()->json([
            'message' => 'A naplóbejegyzés sikeresen létrehozva.',
            'journal_entry' => $journalEntry
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $journalEntry = JournalEntry::with(['user', 'task', 'leaveRequest', 'overtimeRequest'])->find($id);

        if (!$journalEntry) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') naplóbejegyzés nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($journalEntry, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $journalEntry = JournalEntry::find($id);

        if (!$journalEntry) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') naplóbejegyzés nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');

        // Check if user has permission to update this entry
        if (!$isAdmin && $journalEntry->user_id !== $currentUserId) {
            return response()->json([
                'message' => 'Nincs jogosultsága módosítani más felhasználó naplóbejegyzését.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if entry is related to a leave or overtime request
        if ($journalEntry->leaverequest_id || $journalEntry->overtimerequest_id) {
            return response()->json([
                'message' => 'Automatikusan generált (szabadság vagy túlóra) naplóbejegyzés nem módosítható közvetlenül.'
            ], Response::HTTP_FORBIDDEN);
        }

        $rules = [
            'work_date' => ['sometimes', 'date'],
            'hours' => ['sometimes', 'date_format:H:i:s'],
            'work_type' => ['sometimes', 'string', 'max:50'],
            'note' => ['sometimes', 'nullable', 'string'],
            'task_id' => ['sometimes', 'nullable', 'exists:tasks,id'],
        ];

        // Admin can update user_id, regular users cannot
        if ($isAdmin) {
            $rules['user_id'] = ['sometimes', 'exists:users,id'];
        }

        $messages = [
            'work_date.date' => 'A munkanap dátuma érvénytelen formátumú.',

            'hours.date_format' => 'A munkaórák formátuma érvénytelen. A helyes formátum: ÓÓ:PP:MM.',

            'work_type.string' => 'A munka típusa kizárólag nem üres, szöveg formátumú lehet.',
            'work_type.max' => 'A munka típusa maximum 50 karakter hosszú lehet.',

            'note.string' => 'A megjegyzés csak szöveg formátumú lehet.',

            'task_id.exists' => 'A megadott feladat nem létezik.',

            'user_id.exists' => 'A megadott felhasználó nem létezik.',
        ];

        $validated = $request->validate($rules, $messages);

        // For non-admin users, remove user_id if present
        if (!$isAdmin && isset($validated['user_id'])) {
            unset($validated['user_id']);
        }

        // Update the journal entry
        $journalEntry->update($validated);

        // Load relationships for response
        $journalEntry->load(['user', 'task', 'leaveRequest', 'overtimeRequest']);

        return response()->json([
            'message' => 'A naplóbejegyzés sikeresen frissítve.',
            'journal_entry' => $journalEntry
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $journalEntry = JournalEntry::find($id);

        if (!$journalEntry) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') naplóbejegyzés nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');

        // Check if user has permission to delete this entry
        if (!$isAdmin && $journalEntry->user_id !== $currentUserId) {
            return response()->json([
                'message' => 'Nincs jogosultsága törölni más felhasználó naplóbejegyzését.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if entry is related to a leave or overtime request
        if ($journalEntry->leaverequest_id || $journalEntry->overtimerequest_id) {
            return response()->json([
                'message' => 'Automatikusan generált (szabadság vagy túlóra) naplóbejegyzés nem törölhető közvetlenül.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Store information for response message
        $workDate = $journalEntry->work_date->format('Y-m-d');
        $hours = $journalEntry->hours->format('H:i:s');
        $userName = $journalEntry->user->firstname . ' ' . $journalEntry->user->lastname;

        // Delete the journal entry
        $journalEntry->delete();

        return response()->json([
            'message' => "{$userName} naplóbejegyzése ({$workDate}, {$hours}) sikeresen törölve."
        ], Response::HTTP_OK);
    }

    /**
     * Export journal entries based on filters.
     */
    public function export(Request $request)
    {
        // Similar filters as index method
        $query = JournalEntry::query();

        // Apply filters (user_id, task_id, date range, etc.)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->input('task_id'));
        }

        if ($request->has('date_from')) {
            $query->where('work_date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('work_date', '<=', $request->input('date_to'));
        }

        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('work_date', $request->input('month'))
                ->whereYear('work_date', $request->input('year'));
        }

        // Always include relationships for export
        $query->with(['user', 'task', 'leaveRequest', 'overtimeRequest']);

        // Sort by work date and user
        $query->orderBy('work_date', 'asc')->orderBy('user_id', 'asc');

        // Get all entries for export
        $journalEntries = $query->get();

        // Transform data for export (simplified example)
        $exportData = $journalEntries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->work_date->format('Y-m-d'),
                'user' => $entry->user->firstname . ' ' . $entry->user->lastname,
                'hours' => $entry->hours->format('H:i'),
                'work_type' => $entry->work_type,
                'task' => $entry->task ? $entry->task->name : null,
                'project' => $entry->task && $entry->task->project ? $entry->task->project->job_number . ' - ' . $entry->task->project->project_name : null,
                'note' => $entry->note,
            ];
        });

        return response()->json([
            'message' => 'Naplóbejegyzések sikeresen exportálva.',
            'data' => $exportData,
            'count' => $exportData->count()
        ], Response::HTTP_OK);
    }

    /**
     * Get summary statistics for journal entries.
     */
    public function summary(Request $request)
    {
        // Similar filters as index method
        $query = JournalEntry::query();

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('date_from')) {
            $query->where('work_date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('work_date', '<=', $request->input('date_to'));
        }

        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('work_date', $request->input('month'))
                ->whereYear('work_date', $request->input('year'));
        }

        // Get all entries for summary
        $journalEntries = $query->get();

        // Calculate total hours (simplified)
        $totalHours = 0;
        $totalMinutes = 0;

        foreach ($journalEntries as $entry) {
            $time = explode(':', $entry->hours->format('H:i:s'));
            $totalHours += intval($time[0]);
            $totalMinutes += intval($time[1]);
        }

        // Convert excess minutes to hours
        $totalHours += floor($totalMinutes / 60);
        $totalMinutes %= 60;

        // Group by work type
        $workTypeBreakdown = $journalEntries->groupBy('work_type')
            ->map(function ($group) {
                $hours = 0;
                $minutes = 0;

                foreach ($group as $entry) {
                    $time = explode(':', $entry->hours->format('H:i:s'));
                    $hours += intval($time[0]);
                    $minutes += intval($time[1]);
                }

                $hours += floor($minutes / 60);
                $minutes %= 60;

                return [
                    'count' => $group->count(),
                    'total_time' => sprintf('%02d:%02d', $hours, $minutes)
                ];
            });

        // Group by user if no specific user_id is provided
        $userBreakdown = null;
        if (!$request->has('user_id')) {
            $userBreakdown = $journalEntries->groupBy('user_id')
                ->map(function ($group) {
                    $user = $group->first()->user;
                    $hours = 0;
                    $minutes = 0;

                    foreach ($group as $entry) {
                        $time = explode(':', $entry->hours->format('H:i:s'));
                        $hours += intval($time[0]);
                        $minutes += intval($time[1]);
                    }

                    $hours += floor($minutes / 60);
                    $minutes %= 60;

                    return [
                        'user_name' => $user->firstname . ' ' . $user->lastname,
                        'count' => $group->count(),
                        'total_time' => sprintf('%02d:%02d', $hours, $minutes)
                    ];
                });
        }

        // Return the summary data
        return response()->json([
            'total_entries' => $journalEntries->count(),
            'total_time' => sprintf('%02d:%02d', $totalHours, $totalMinutes),
            'work_type_breakdown' => $workTypeBreakdown,
            'user_breakdown' => $userBreakdown,
        ], Response::HTTP_OK);
    }
}
