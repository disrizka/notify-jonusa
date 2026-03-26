<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresenceController; // WAJIB DIIMPORT
use App\Http\Controllers\Admin\PresenceApprovalController;

use App\Http\Controllers\Admin\OfficeSettingController;
// Route Login (Tanpa Auth)
Route::post('/login', [AuthApiController::class, 'login']);

// Route yang butuh login (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Logout
    Route::post('/logout', [AuthApiController::class, 'logout']);
    
    // Ambil data profil user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/presence/permissions', [PresenceController::class, 'storePermission']);
    Route::post('/presence/check-in', [PresenceController::class, 'storeCheckIn']);
    Route::get('/attendance/config', [OfficeSettingController::class, 'getConfig']);
    Route::post('/presence/checkout', [PresenceController::class, 'storeCheckOut']);
  
    
    Route::get('/chats', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::post('/chats', [\App\Http\Controllers\Api\ChatController::class, 'store']);

    Route::get('/users', function () {
        return \App\Models\User::all(); 
    });

    Route::get('/presence/today', [PresenceController::class, 'todayStatus']);
    
});