<?php

namespace App\Http\Controllers\Api\WorkLog;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by', 'deadline');
            $sortDir = $request->input('sort_dir', 'asc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('deadline', 'asc');
        }

        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $allowedIncludes = ['address', 'tasks', 'tasks.users'];

            foreach ($includes as $include) {
                if (in_array($include, $allowedIncludes)) {
                    $query->with($include);
                }
            }
        }

        if ($request->has('per_page')) {
            $projects = $query->paginate($request->input('per_page', 15));
        } else {
            $projects = $query->get();
        }

        return response()->json($projects, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_number' => [
                'required',
                'string',
                'max:50',
                'unique:projects',
                'regex:/^\d{4}\.\d{3}$/',
                function ($attribute, $value, $fail) {
                    $year = explode('.', $value)[0] ?? '';
                    $currentYear = date('Y');

                    if ($year != $currentYear) {
                        $fail('A munkaszám év része nem egyezik az aktuális évvel.');
                    }
                },
            ],
            'project_name' => ['required', 'string', 'max:75'],
            'location' => ['nullable', 'string', 'max:100'],
            'parcel_identification_number' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50', 'in:várakozó,befejezett,folyamatban lévő,felfüggesztett'],
            'address_id' => ['nullable', 'exists:addresses,id'],
        ], [
            'job_number.required' => 'A munkaszám megadása kötelező.',
            'job_number.string' => 'A munkaszám csak szöveg formátumú lehet.',
            'job_number.max' => 'A munkaszám maximum 50 karakter hosszú lehet.',
            'job_number.unique' => 'Ez a munkaszám már foglalt.',
            'job_number.regex' => 'A munkaszám formátuma érvénytelen. A helyes formátum: ÉÉÉÉ.XXX (pl. 2025.001).',

            'project_name.required' => 'A projekt név megadása kötelező.',
            'project_name.string' => 'A projekt név csak szöveg formátumú lehet.',
            'project_name.max' => 'A projekt név maximum 75 karakter hosszú lehet.',

            'location.string' => 'A helyszín csak szöveg formátumú lehet.',
            'location.max' => 'A helyszín maximum 100 karakter hosszú lehet.',

            'parcel_identification_number.string' => 'A helyrajzi szám csak szöveg formátumú lehet.',
            'parcel_identification_number.max' => 'A helyrajzi szám maximum 100 karakter hosszú lehet.',

            'deadline.date' => 'A határidő érvénytelen dátum formátumú.',

            'description.string' => 'A leírás csak szöveg formátumú lehet.',

            'status.required' => 'A státusz megadása kötelező.',
            'status.string' => 'A státusz csak szöveg formátumú lehet.',
            'status.max' => 'A státusz maximum 50 karakter hosszú lehet.',
            'status.in' => 'A státusz csak a következők egyike lehet: várakozó, befejezett, folyamatban lévő, felfüggesztett.',

            'address_id.exists' => 'A megadott cím nem létezik.',
        ]);

        $project = Project::create($validated);

        return response()->json([
            'message' => 'A projekt sikeresen létrehozva.',
            'project' => $project
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with(['address', 'tasks.users'])->find($id);

        if (!$project) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') projekt nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($project, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') projekt nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'job_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('projects')->ignore($project->id),
                'regex:/^\d{4}\.\d{3}$/',
                function ($attribute, $value, $fail) {
                    $year = explode('.', $value)[0] ?? '';
                    $currentYear = date('Y');

                    if ($year != $currentYear) {
                        $fail('A munkaszám év része nem egyezik az aktuális évvel.');
                    }
                },
            ],
            'project_name' => ['sometimes', 'string', 'max:75'],
            'location' => ['sometimes', 'nullable', 'string', 'max:100'],
            'parcel_identification_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'deadline' => ['sometimes', 'nullable', 'date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', 'max:50', 'in:várakozó,befejezett,folyamatban lévő,felfüggesztett'],
            'address_id' => ['sometimes', 'nullable', 'exists:addresses,id'],
        ], [
            'job_number.string' => 'A munkaszám kizárólag nem üres, szöveg formátumú lehet.',
            'job_number.max' => 'A munkaszám maximum 50 karakter hosszú lehet.',
            'job_number.unique' => 'Ez a munkaszám már foglalt.',
            'job_number.regex' => 'A munkaszám formátuma érvénytelen. A helyes formátum: ÉÉÉÉ.XXX (pl. 2025.001).',

            'project_name.string' => 'A projekt név kizárólag nem üres, szöveg formátumú lehet.',
            'project_name.max' => 'A projekt név maximum 75 karakter hosszú lehet.',

            'location.string' => 'A helyszín csak szöveg formátumú lehet.',
            'location.max' => 'A helyszín maximum 100 karakter hosszú lehet.',

            'parcel_identification_number.string' => 'A helyrajzi szám csak szöveg formátumú lehet.',
            'parcel_identification_number.max' => 'A helyrajzi szám maximum 100 karakter hosszú lehet.',

            'deadline.date' => 'A határidő érvénytelen dátum formátumú.',

            'description.string' => 'A leírás csak szöveg formátumú lehet.',

            'status.string' => 'A státusz kizárólag nem üres, szöveg formátumú lehet.',
            'status.max' => 'A státusz maximum 50 karakter hosszú lehet.',
            'status.in' => 'A státusz csak a következők egyike lehet: várakozó, befejezett, folyamatban lévő, felfüggesztett.',

            'address_id.exists' => 'A megadott cím nem létezik.',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'A projekt adatai sikeresen frissítve lettek.',
            'project' => $project
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'message' => 'A megadott azonosítójú (ID: ' . $id . ') projekt nem található.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($project->tasks()->count() > 0) {
            return response()->json([
                'message' => 'Ez a projekt feladatokhoz van rendelve, ezért nem törölhető.'
            ], Response::HTTP_FORBIDDEN);
        }

        $projectInfo = $project->job_number . ' (' . $project->project_name . ')';

        $project->delete();

        return response()->json([
            'message' => "{$projectInfo} projekt sikeresen törölve."
        ], Response::HTTP_OK);
    }
}
