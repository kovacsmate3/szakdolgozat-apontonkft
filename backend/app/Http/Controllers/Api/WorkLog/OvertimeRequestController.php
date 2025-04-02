<?php

namespace App\Http\Controllers\Api\WorkLog;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OvertimeRequest::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->input('date_to'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('decision_comment', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'date');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('date', 'desc');
        }

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['user', 'approver', 'journalEntry'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        if ($request->has('per_page')) {
            $overtimeRequests = $query->paginate($request->input('per_page', 15));
        } else {
            $overtimeRequests = $query->get();
        }

        return response()->json($overtimeRequests, Response::HTTP_OK);
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
            'date' => ['required', 'date'],
            'hours' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string'],
        ];

        if ($isAdmin) {
            $rules['user_id'] = [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($currentUserId) {
                    if ($value == $currentUserId) {
                        $fail('Adminisztrátor nem vehet fel magának túlóra igényt.');
                    }
                }
            ];
            $rules['status'] = [
                'sometimes',
                'string',
                'max:50',
                'in:függőben lévő,jóváhagyott,elutasított'
            ];
            $rules['decision_comment'] = [
                'sometimes',
                'required_if:status,elutasított',
                'string',
                'max:100'
            ];
        }

        $messages = [
            'user_id.required' => 'A felhasználó azonosítójának megadása kötelező.',
            'user_id.exists' => 'A megadott felhasználó nem létezik.',

            'date.required' => 'A túlóra dátumának megadása kötelező.',
            'date.date' => 'A túlóra dátuma érvénytelen formátumú.',

            'hours.required' => 'A túlóra időtartamának megadása kötelező.',
            'hours.date_format' => 'Az időtartam formátuma érvénytelen. A helyes formátum: ÓÓ:PP.',

            'reason.required' => 'A túlóra indoklásának megadása kötelező.',
            'reason.string' => 'Az indoklás csak szöveg formátumú lehet.',

            'status.string' => 'A státusz csak szöveg formátumú lehet.',
            'status.max' => 'A státusz maximum 50 karakter hosszú lehet.',
            'status.in' => 'A státusz csak a következők egyike lehet: függőben lévő, jóváhagyott, elutasított.',

            'processed_at.date' => 'A feldolgozás dátuma érvénytelen formátumú.',

            'processed_by.exists' => 'A megadott jóváhagyó felhasználó nem létezik.',

            'decision_comment.string' => 'A döntés megjegyzése csak szöveg formátumú lehet.',
            'decision_comment.max' => 'A döntés megjegyzése maximum 100 karakter hosszú lehet.',
            'decision_comment.required_if' => 'Túlóra igény elutasításakor kötelező megadni az elutasítás indokát.',
        ];

        $validated = $request->validate($rules, $messages);

        if (!$isAdmin) {
            $keysToFilter = ['user_id', 'status', 'processed_at', 'processed_by', 'decision_comment'];
            foreach ($keysToFilter as $key) {
                if (isset($validated[$key])) {
                    unset($validated[$key]);
                }
            }
        }

        if (!$isAdmin) {
            $validated['user_id'] = Auth::id();
        }

        $exists = OvertimeRequest::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Erre a napra már létezik túlóraigény a megadott felhasználóhoz.',
            ], Response::HTTP_CONFLICT);
        }

        if (!$isAdmin) {
            $validated['status'] = 'függőben lévő';
            $validated['processed_at'] = null;
            $validated['processed_by'] = null;
            $validated['decision_comment'] = null;
        } else {
            if (isset($validated['status']) && in_array($validated['status'], ['jóváhagyott', 'elutasított'])) {
                $validated['processed_at'] = now();
                $validated['processed_by'] = Auth::id();
            } else {
                $validated['status'] = $validated['status'] ?? 'függőben lévő';
                $validated['processed_at'] = null;
                $validated['processed_by'] = null;
                $validated['decision_comment'] = null;
            }
        }

        $overtimeRequest = OvertimeRequest::create($validated);
        if ($overtimeRequest->status === 'jóváhagyott') {
            $this->createJournalEntryForOvertimeRequest($overtimeRequest);
        }

        $overtimeRequest->load(['user', 'approver', 'journalEntry']);


        return response()->json([
            'message' => 'A túlóra igény sikeresen létrehozva.',
            'overtime_request' => $overtimeRequest
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $overtimeRequest = OvertimeRequest::with(['user', 'approver', 'journalEntry'])->find($id);

        if (!$overtimeRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') túlóra igény nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($overtimeRequest, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $overtimeRequest = OvertimeRequest::find($id);

        if (!$overtimeRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') túlóra igény nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');

        if (!$isAdmin && $overtimeRequest->user_id !== $currentUserId) {
            return response()->json([
                'message' => 'Nincs jogosultsága módosítani más felhasználó túlóra igényét.'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$isAdmin && $overtimeRequest->status !== 'függőben lévő') {
            return response()->json([
                'message' => 'Már feldolgozott túlóra igényt csak adminisztrátor módosíthat.'
            ], Response::HTTP_FORBIDDEN);
        }

        $rules = [
            'date' => ['sometimes', 'date'],
            'hours' => ['sometimes', 'date_format:H:i'],
            'reason' => ['sometimes', 'string'],
        ];

        if ($isAdmin) {
            $rules['user_id'] = [
                'sometimes',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($currentUserId) {
                    if ($value == $currentUserId) {
                        $fail('Adminisztrátor nem módosíthatja a túlóra igényt saját magára.');
                    }
                }
            ];
            $rules['status'] = [
                'sometimes',
                'string',
                'max:50',
                'in:függőben lévő,jóváhagyott,elutasított'
            ];
            $rules['decision_comment'] = [
                'required_if:status,elutasított',
                'string',
                'max:100'
            ];
        }

        $messages = [
            'user_id.exists' => 'A megadott felhasználó nem létezik.',
            'date.date' => 'A túlóra dátuma érvénytelen formátumú.',
            'hours.date_format' => 'Az időtartam formátuma érvénytelen. A helyes formátum: ÓÓ:PP.',
            'reason.string' => 'Az indoklás kizárólag nem üres, szöveg formátumú lehet.',
            'status.string' => 'A státusz kizárólag nem üres, szöveg formátumú lehet.',
            'status.max' => 'A státusz maximum 50 karakter hosszú lehet.',
            'status.in' => 'A státusz csak a következők egyike lehet: függőben lévő, jóváhagyott, elutasított.',
            'decision_comment.string' => 'A döntés megjegyzése csak szöveg formátumú lehet.',
            'decision_comment.max' => 'A döntés megjegyzése maximum 100 karakter hosszú lehet.',
            'decision_comment.required_if' => 'Túlóra igény elutasításakor kötelező megadni az elutasítás indokát.',
        ];

        $validated = $request->validate($rules, $messages);

        if (!$isAdmin) {
            $keysToFilter = ['user_id', 'status', 'processed_at', 'processed_by', 'decision_comment'];
            foreach ($keysToFilter as $key) {
                if (isset($validated[$key])) {
                    unset($validated[$key]);
                }
            }
        }

        if (isset($validated['date']) || isset($validated['user_id'])) {
            $userId = $validated['user_id'] ?? $overtimeRequest->user_id;
            $date = $validated['date'] ?? $overtimeRequest->date;

            $exists = OvertimeRequest::where('user_id', $userId)
                ->where('date', $date)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Erre a napra már létezik túlóraigény a megadott felhasználóhoz.',
                ], Response::HTTP_CONFLICT);
            }
        }

        if ($isAdmin && isset($validated['status'])) {
            $oldStatus = $overtimeRequest->status;
            $newStatus = $validated['status'];

            if ($oldStatus === 'függőben lévő' && ($newStatus === 'jóváhagyott' || $newStatus === 'elutasított')) {
                $validated['processed_at'] = now();
                $validated['processed_by'] = $currentUserId;
            } else if (($oldStatus === 'jóváhagyott' || $oldStatus === 'elutasított') && $newStatus === 'függőben lévő') {
                $validated['processed_at'] = null;
                $validated['processed_by'] = null;
                if (!isset($validated['decision_comment'])) {
                    $validated['decision_comment'] = null;
                }
            }
        }

        $oldStatus = $overtimeRequest->status;

        $overtimeRequest->update($validated);

        if ($isAdmin && isset($validated['status']) && $oldStatus !== $validated['status']) {
            if ($oldStatus === 'jóváhagyott' && $validated['status'] !== 'jóváhagyott') {
                JournalEntry::where('overtimerequest_id', $overtimeRequest->id)->delete();
            } elseif ($oldStatus !== 'jóváhagyott' && $validated['status'] === 'jóváhagyott') {
                $this->createJournalEntryForOvertimeRequest($overtimeRequest);
            }
        }

        $overtimeRequest->load(['user', 'approver', 'journalEntry']);

        return response()->json([
            'message' => 'A túlóra igény adatai sikeresen frissítve lettek.',
            'overtime_request' => $overtimeRequest
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $overtimeRequest = OvertimeRequest::find($id);

        if (!$overtimeRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') túlóra igény nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');
        $isOwnRequest = ($overtimeRequest->user_id === $currentUserId);
        $isPending = ($overtimeRequest->status === 'függőben lévő');

        if (!$isAdmin && (!$isOwnRequest || !$isPending)) {
            return response()->json([
                'message' => 'Nem megfelelő jogosultság a törléshez. Csak saját függőben lévő túlóra igényeket törölhet, vagy adminisztrátor jogosultság szükséges.'
            ], Response::HTTP_FORBIDDEN);
        }

        $date = $overtimeRequest->date->format('Y-m-d');
        $hours = $overtimeRequest->hours->format('H:i');
        $userName = optional($overtimeRequest->user)->firstname . ' ' . optional($overtimeRequest->user)->lastname;

        $overtimeRequest->delete();

        return response()->json([
            'message' => "{$userName} túlóra igénye ({$date}, {$hours}) sikeresen törölve."
        ], Response::HTTP_OK);
    }

    /**
     * Approve the overtime request.
     */
    public function approve(Request $request, string $id)
    {
        $overtimeRequest = OvertimeRequest::find($id);

        if (!$overtimeRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') túlóra igény nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;

        if ($userRole !== 'admin') {
            return response()->json([
                'message' => 'Túlóra igény jóváhagyásához adminisztrátor jogosultság szükséges.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($overtimeRequest->status !== 'függőben lévő') {
            return response()->json([
                'message' => 'Csak függőben lévő túlóra igény hagyható jóvá.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($overtimeRequest->user_id == $currentUserId) {
            return response()->json([
                'message' => 'Admin saját túlóra igényének jóváhagyása nem megengedett.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'decision_comment' => ['nullable', 'string', 'max:100'],
        ], [
            'decision_comment.string' => 'A döntés megjegyzése csak szöveg formátumú lehet.',
            'decision_comment.max' => 'A döntés megjegyzése maximum 100 karakter hosszú lehet.',
        ]);

        $overtimeRequest->update([
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'decision_comment' => $validated['decision_comment'] ?? null,
        ]);

        $this->createJournalEntryForOvertimeRequest($overtimeRequest);

        $overtimeRequest->load(['user', 'approver', 'journalEntry']);

        return response()->json([
            'message' => 'A túlóra igény sikeresen jóváhagyva.',
            'overtime_request' => $overtimeRequest
        ], Response::HTTP_OK);
    }

    /**
     * Reject the overtime request.
     */
    public function reject(Request $request, string $id)
    {
        $overtimeRequest = OvertimeRequest::find($id);

        if (!$overtimeRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') túlóra igény nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;

        if ($userRole !== 'admin') {
            return response()->json([
                'message' => 'Túlóra igény elutasításához adminisztrátor jogosultság szükséges.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($overtimeRequest->status !== 'függőben lévő') {
            return response()->json([
                'message' => 'Csak függőben lévő túlóra igény utasítható el.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        if ($overtimeRequest->user_id == $currentUserId) {
            return response()->json([
                'message' => 'Admin saját túlóra igényének elutasítása nem megengedett.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'decision_comment' => ['required', 'string', 'max:100'],
        ], [
            'decision_comment.required' => 'Az elutasítás indokának megadása kötelező.',
            'decision_comment.string' => 'Az elutasítás indoka csak szöveg formátumú lehet.',
            'decision_comment.max' => 'Az elutasítás indoka maximum 100 karakter hosszú lehet.',
        ]);

        $overtimeRequest->update([
            'status' => 'elutasított',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'decision_comment' => $validated['decision_comment'],
        ]);

        $overtimeRequest->load(['user', 'approver']);

        return response()->json([
            'message' => 'A túlóra igény elutasítva.',
            'overtime_request' => $overtimeRequest
        ], Response::HTTP_OK);
    }

    /**
     * Létrehozza a naplóbejegyzést egy jóváhagyott túlóra kérelemhez
     *
     * @param OvertimeRequest $overtimeRequest
     * @return JournalEntry|null A létrehozott naplóbejegyzés vagy null
     */
    private function createJournalEntryForOvertimeRequest(OvertimeRequest $overtimeRequest)
    {
        if ($overtimeRequest->status !== 'jóváhagyott') {
            return null;
        }

        $userFirstName = $overtimeRequest->user->firstname ?? '';
        $reason = $overtimeRequest->reason ?? '';

        return JournalEntry::create([
            'work_date' => $overtimeRequest->date,
            'hours' => $overtimeRequest->hours,
            'work_type' => 'túlóra',
            'overtimerequest_id' => $overtimeRequest->id,
            'user_id' => $overtimeRequest->user_id,
            'task_id' => null,
            'note' => "TÚLÓRA: {$userFirstName} - {$reason}"
        ]);
    }
}
