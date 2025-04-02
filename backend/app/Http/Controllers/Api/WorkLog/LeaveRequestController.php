<?php

namespace App\Http\Controllers\Api\WorkLog;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('end_date', '<=', $request->input('date_to'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('decision_comment', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'start_date');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('start_date', 'desc');
        }

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['user', 'approver', 'journalEntries'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        if ($request->has('per_page')) {
            $leaveRequests = $query->paginate($request->input('per_page', 15));
        } else {
            $leaveRequests = $query->get();
        }

        return response()->json($leaveRequests, Response::HTTP_OK);
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
        ];

        if ($isAdmin) {
            $rules['user_id'] = [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($currentUserId) {
                    if ($value == $currentUserId) {
                        $fail('Adminisztrátor nem vehet fel magának szabadság igényt.');
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

            'start_date.required' => 'A szabadság kezdetének megadása kötelező.',
            'start_date.date' => 'A szabadság kezdete érvénytelen formátumú.',

            'end_date.required' => 'A szabadság végének megadása kötelező.',
            'end_date.date' => 'A szabadság vége érvénytelen formátumú.',
            'end_date.after_or_equal' => 'A szabadság vége nem lehet korábbi, mint a kezdete.',

            'reason.required' => 'A szabadság indoklásának megadása kötelező.',
            'reason.string' => 'Az indoklás csak szöveg formátumú lehet.',

            'status.string' => 'A státusz csak szöveg formátumú lehet.',
            'status.max' => 'A státusz maximum 50 karakter hosszú lehet.',
            'status.in' => 'A státusz csak a következők egyike lehet: függőben lévő, jóváhagyott, elutasított.',

            'processed_at.date' => 'A feldolgozás dátuma érvénytelen formátumú.',

            'processed_by.exists' => 'A megadott jóváhagyó felhasználó nem létezik.',

            'decision_comment.string' => 'A döntés megjegyzése csak szöveg formátumú lehet.',
            'decision_comment.max' => 'A döntés megjegyzése maximum 100 karakter hosszú lehet.',
            'decision_comment.required_if' => 'Szabadság kérelem elutasításakor kötelező megadni az elutasítás indokát.',
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

        $overlapping = LeaveRequest::where('user_id', $validated['user_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->where('status', '!=', 'elutasított')
            ->exists();

        if ($overlapping) {
            return response()->json([
                'message' => 'A megadott időszakra már létezik szabadság kérelem a felhasználóhoz.',
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

        $leaveRequest = LeaveRequest::create($validated);
        $leaveRequest->load(['user', 'approver']);

        return response()->json([
            'message' => 'A szabadság kérelem sikeresen létrehozva.',
            'leave_request' => $leaveRequest
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $leaveRequest = LeaveRequest::with(['user', 'approver', 'journalEntries'])->find($id);

        if (!$leaveRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szabadság kérelem nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($leaveRequest, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szabadság kérelem nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');

        if (!$isAdmin && $leaveRequest->user_id !== $currentUserId) {
            return response()->json([
                'message' => 'Nincs jogosultsága módosítani más felhasználó szabadság kérelmét.'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$isAdmin && $leaveRequest->status !== 'függőben lévő') {
            return response()->json([
                'message' => 'Már feldolgozott szabadság kérelmet csak adminisztrátor módosíthat.'
            ], Response::HTTP_FORBIDDEN);
        }

        $rules = [
            'start_date' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) use ($request, $leaveRequest) {
                    $endDate = $request->input('end_date', $leaveRequest->end_date);
                    if ($value > $endDate) {
                        $fail('A szabadság kezdete nem lehet későbbi, mint a vége.');
                    }
                }
            ],
            'end_date' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) use ($request, $leaveRequest) {
                    $startDate = $request->input('start_date', $leaveRequest->start_date);
                    if ($value < $startDate) {
                        $fail('A szabadság vége nem lehet korábbi, mint a kezdete.');
                    }
                }
            ],
            'reason' => ['sometimes', 'string'],
        ];

        if ($isAdmin) {
            $rules['user_id'] = [
                'sometimes',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($currentUserId) {
                    if ($value == $currentUserId) {
                        $fail('Adminisztrátor nem módosíthatja a szabadság kérelmet saját magára.');
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
            'start_date.date' => 'A szabadság kezdete érvénytelen formátumú.',
            'end_date.date' => 'A szabadság vége érvénytelen formátumú.',
            'reason.string' => 'Az indoklás kizárólag nem üres, szöveg formátumú lehet.',
            'status.string' => 'A státusz kizárólag nem üres, szöveg formátumú lehet.',
            'status.max' => 'A státusz maximum 50 karakter hosszú lehet.',
            'status.in' => 'A státusz csak a következők egyike lehet: függőben lévő, jóváhagyott, elutasított.',
            'decision_comment.string' => 'A döntés megjegyzése csak szöveg formátumú lehet.',
            'decision_comment.max' => 'A döntés megjegyzése maximum 100 karakter hosszú lehet.',
            'decision_comment.required_if' => 'Szabadság igény elutasításakor kötelező megadni az elutasítás indokát.',
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

        if (isset($validated['start_date']) || isset($validated['end_date']) || isset($validated['user_id'])) {
            $userId = $validated['user_id'] ?? $leaveRequest->user_id;
            $startDate = $validated['start_date'] ?? $leaveRequest->start_date;
            $endDate = $validated['end_date'] ?? $leaveRequest->end_date;

            $overlapping = LeaveRequest::where('user_id', $userId)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->where('status', '!=', 'elutasított')
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'message' => 'A megadott időszakra már létezik szabadság kérelem a felhasználóhoz.',
                ], Response::HTTP_CONFLICT);
            }
        }

        if ($isAdmin && isset($validated['status'])) {
            $oldStatus = $leaveRequest->status;
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

        $leaveRequest->update($validated);
        $leaveRequest->load(['user', 'approver', 'journalEntries']);

        return response()->json([
            'message' => 'A szabadság kérelem adatai sikeresen frissítve lettek.',
            'leave_request' => $leaveRequest
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szabadság kérelem nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;
        $isAdmin = ($userRole === 'admin');
        $isOwnRequest = ($leaveRequest->user_id === $currentUserId);
        $isPending = ($leaveRequest->status === 'függőben lévő');

        if (!$isAdmin && (!$isOwnRequest || !$isPending)) {
            return response()->json([
                'message' => 'Nem megfelelő jogosultság a törléshez. Csak saját függőben lévő szabadság kérelmeket törölhet, vagy adminisztrátor jogosultság szükséges.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($leaveRequest->journalEntries()->count() > 0) {
            return response()->json([
                'message' => 'Ez a szabadság kérelem már rögzítve van a munkanaplóban, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $reason = $leaveRequest->reason;
        $startDate = $leaveRequest->start_date->format('Y-m-d');
        $endDate = $leaveRequest->end_date->format('Y-m-d');
        $userName = optional($leaveRequest->user)->firstname . ' ' . optional($leaveRequest->user)->lastname;

        $leaveRequest->delete();

        return response()->json([
            'message' => "{$userName} szabadság kérelme ({$reason}: {$startDate} - {$endDate}) sikeresen törölve."
        ], Response::HTTP_OK);
    }

    /**
     * Approve the leave request.
     */
    public function approve(Request $request, string $id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szabadság kérelem nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;

        if ($userRole !== 'admin') {
            return response()->json([
                'message' => 'Szabadság kérelem jóváhagyásához adminisztrátor jogosultság szükséges.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($leaveRequest->status !== 'függőben lévő') {
            return response()->json([
                'message' => 'Csak függőben lévő szabadság kérelem hagyható jóvá.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($leaveRequest->user_id == $currentUserId) {
            return response()->json([
                'message' => 'Admin saját szabadság kérelmének jóváhagyása nem megengedett.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'decision_comment' => ['nullable', 'string', 'max:100'],
        ], [
            'decision_comment.string' => 'A döntés megjegyzése csak szöveg formátumú lehet.',
            'decision_comment.max' => 'A döntés megjegyzése maximum 100 karakter hosszú lehet.',
        ]);

        $leaveRequest->update([
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'decision_comment' => $validated['decision_comment'] ?? null,
        ]);

        $leaveRequest->load(['user', 'approver']);

        return response()->json([
            'message' => 'A szabadság kérelem sikeresen jóváhagyva.',
            'leave_request' => $leaveRequest
        ], Response::HTTP_OK);
    }

    /**
     * Reject the leave request.
     */
    public function reject(Request $request, string $id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') szabadság kérelem nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentUserId = Auth::id();
        $userRole = Auth::user()->role->slug ?? null;

        if ($userRole !== 'admin') {
            return response()->json([
                'message' => 'Szabadság kérelem elutasításához adminisztrátor jogosultság szükséges.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($leaveRequest->status !== 'függőben lévő') {
            return response()->json([
                'message' => 'Csak függőben lévő szabadság kérelem utasítható el.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($leaveRequest->user_id == $currentUserId) {
            return response()->json([
                'message' => 'Admin saját szabadság kérelmének elutasítása nem megengedett.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'decision_comment' => ['required', 'string', 'max:100'],
        ], [
            'decision_comment.required' => 'Az elutasítás indokának megadása kötelező.',
            'decision_comment.string' => 'Az elutasítás indoka csak szöveg formátumú lehet.',
            'decision_comment.max' => 'Az elutasítás indoka maximum 100 karakter hosszú lehet.',
        ]);

        $leaveRequest->update([
            'status' => 'elutasított',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
            'decision_comment' => $validated['decision_comment'],
        ]);

        $leaveRequest->load(['user', 'approver']);

        return response()->json([
            'message' => 'A szabadság kérelem elutasítva.',
            'leave_request' => $leaveRequest
        ], Response::HTTP_OK);
    }
}
