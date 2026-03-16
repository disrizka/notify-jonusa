<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PresenceController; // WAJIB DIIMPORT
use App\Http\Controllers\Admin\PresenceApprovalController;

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

    // KIRIM ABSENSI (Dari Flutter)
    // Gunakan PresenceController (bukan AttendanceController)
    Route::post('/presence/check-in', [PresenceController::class, 'storeCheckIn']);
    Route::get('/attendance/config', [PresenceApprovalController::class, 'getConfig']);
    Route::post('/presence/checkout', [PresenceController::class, 'storeCheckOut']);
    
});