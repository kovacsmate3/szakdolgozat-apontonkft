<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoadRecord\CarController;
use App\Http\Controllers\Api\RoadRecord\FuelExpenseController;
use App\Http\Controllers\Api\RoadRecord\FuelPriceController;
use App\Http\Controllers\Api\RoadRecord\LocationController;
use App\Http\Controllers\Api\RoadRecord\LocationPurposeController;
use App\Http\Controllers\Api\RoadRecord\TravelPurposeDictionaryController;
use App\Http\Controllers\Api\RoadRecord\TripController;
use App\Http\Controllers\Api\Shared\AddressController;
use App\Http\Controllers\Api\Shared\LawCategoryController;
use App\Http\Controllers\Api\Shared\LawController;
use App\Http\Controllers\Api\Shared\UserManagement\PermissionController;
use App\Http\Controllers\Api\Shared\UserManagement\RoleController;
use App\Http\Controllers\Api\Shared\UserManagement\RolePermissionController;
use App\Http\Controllers\Api\Shared\UserManagement\UserController;
use App\Http\Controllers\Api\WorkLog\JournalEntryController;
use App\Http\Controllers\Api\WorkLog\LeaveRequestController;
use App\Http\Controllers\Api\WorkLog\OvertimeRequestController;
use App\Http\Controllers\Api\WorkLog\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Üdvözöljük az A-Ponton Kft. API felületén!',
        'company' => 'A-Ponton Kft.',
        'services' => [
            'Geodéziai felmérések',
            'Kitűzések',
            'Ingatlan-nyilvántartási feladatok',
            'Épületfeltüntetés'
        ],
        'status' => 'Az API működik',
        'version' => '1.0',
        'date' => now()->format('Y-m-d H:i:s')
    ]);
});

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // UserController
    Route::apiResource('users', UserController::class);
    // PermissionController
    Route::apiResource('permissions', PermissionController::class);
    // RoleController
    Route::apiResource('roles', RoleController::class);

    // RolePermissionController
    Route::get('roles/{roleId}/permissions', [RolePermissionController::class, 'index']);
    Route::post('roles/{roleId}/permissions', [RolePermissionController::class, 'store']);
    Route::get('roles/{roleId}/permissions/{permissionId}', [RolePermissionController::class, 'show']);
    Route::put('roles/{roleId}/permissions/{permissionId}', [RolePermissionController::class, 'update']);
    Route::delete('roles/{roleId}/permissions/{permissionId}', [RolePermissionController::class, 'destroy']);
    Route::post('roles/{roleId}/permissions/sync', [RolePermissionController::class, 'sync']);

    // AddressController
    Route::apiResource('addresses', AddressController::class);
    // LawCategoryController
    Route::apiResource('law-categories', LawCategoryController::class);
    // lawController
    Route::apiResource('laws', LawController::class);
    // CarController
    Route::apiResource('cars', CarController::class);
    // FuelPriceController
    Route::apiResource('fuel-prices', FuelPriceController::class);
    // LocationController
    Route::apiResource('locations', LocationController::class);
    // TravelPurposeDictionaryController
    Route::apiResource('travel-purpose-dictionaries', TravelPurposeDictionaryController::class);

    // LocationPurposeController
    Route::get('locations/{locationId}/travel-purposes', [LocationPurposeController::class, 'index']);
    Route::post('locations/{locationId}/travel-purposes', [LocationPurposeController::class, 'store']);
    Route::get('locations/{locationId}/travel-purposes/{travelPurposeId}', [LocationPurposeController::class, 'show']);
    Route::delete('locations/{locationId}/travel-purposes/{travelPurposeId}', [LocationPurposeController::class, 'destroy']);
    Route::post('locations/{locationId}/travel-purposes/sync', [LocationPurposeController::class, 'sync']);

    // FuelExpenseController
    Route::apiResource('fuel-expenses', FuelExpenseController::class);
    // TripController
    Route::apiResource('trips', TripController::class);
    Route::post('trips/export', [TripController::class, 'export']);
    // ProjectController
    Route::apiResource('projects', ProjectController::class);
    // OvertimeRequestController
    Route::apiResource('overtime-requests', OvertimeRequestController::class);
    Route::post('overtime-requests/{id}/approve', [OvertimeRequestController::class, 'approve']);
    Route::post('overtime-requests/{id}/reject', [OvertimeRequestController::class, 'reject']);
    // LeaveRequestController
    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
    // JournalEntryController
    Route::apiResource('journal-entries', JournalEntryController::class);
});
