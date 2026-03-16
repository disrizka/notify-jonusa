<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DivisionController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\Admin\PresenceApprovalController;
use App\Http\Controllers\Admin\LeaveApprovalController;
// Tambahkan Controller baru di sini
use App\Http\Controllers\Admin\OfficeSettingController; 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'kepala') {
        return view('dashboard');
    }
    
    if ($user->division && $user->division->name === 'Customer Service') {
        return redirect()->route('jobs.create');
    }

    return redirect()->route('technician.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- FITUR KEHADIRAN & IZIN (KHUSUS ADMIN/KEPALA) ---
    Route::middleware('role:kepala')->group(function () {
        
        // 1. Menu Utama Approval
        Route::get('/admin/attendance/approval', [PresenceApprovalController::class, 'index'])->name('admin.presence.index');
        
        // 2. Aksi Setuju/Tolak
        Route::post('/admin/attendance/approval/{id}/{status}', [PresenceApprovalController::class, 'updateStatus'])->name('admin.presence.updateStatus');

        // 3. Sub-menu: Jadwal Kerja
        Route::get('/admin/attendance/schedule', [PresenceApprovalController::class, 'schedule'])->name('admin.presence.schedule');
        Route::post('/admin/attendance/schedule/update', [PresenceApprovalController::class, 'updateSchedule'])->name('admin.presence.updateSchedule');

        // 4. Sub-menu: Presensi (Riwayat)
        Route::get('/admin/attendance/history', [PresenceApprovalController::class, 'history'])->name('admin.presence.history');

        // 5. Sub-menu: Izin & Cuti
        Route::get('/admin/leaves/approval', [PresenceApprovalController::class, 'leaveIndex'])->name('admin.leaves.index');
        Route::post('/admin/leaves/{id}/approve', [PresenceApprovalController::class, 'leaveApprove'])->name('admin.leaves.approve');
        Route::post('/admin/leaves/{id}/reject', [PresenceApprovalController::class, 'leaveReject'])->name('admin.leaves.reject');

        // 6. Settings Absensi (RADIUS, JAM MASUK/PULANG, TOLERANSI)
        // Kita gunakan OfficeSettingController agar lebih spesifik
       // Route untuk menampilkan halaman
Route::get('/admin/attendance/settings', [OfficeSettingController::class, 'index'])->name('admin.presence.settings');

// Route untuk proses simpan (POST)
Route::post('/admin/attendance/settings/update', [OfficeSettingController::class, 'update'])->name('admin.presence.updateSettings');
    });

    // Admin Only: Division & User Management
    Route::resource('divisions', DivisionController::class);
    Route::resource('users-management', UserController::class);

    // Job System
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/history', [JobController::class, 'history'])->name('jobs.history');
    Route::post('/jobs/{job}/feedback', [JobController::class, 'storeFeedback'])->name('jobs.feedback');

    // Technician System
    Route::get('/technician/dashboard', [JobController::class, 'technicianDashboard'])->name('technician.dashboard');
    Route::post('/jobs/{job}/accept', [JobController::class, 'acceptJob'])->name('jobs.accept');
    Route::post('/jobs/{job}/progress', [JobController::class, 'updateProgress'])->name('jobs.progress');

    // --- DYNAMIC CHECKLIST SYSTEM ---
    Route::get('/admin/checklists/create', [ChecklistController::class, 'createTemplate'])->name('admin.createTemplate');
    Route::post('/admin/checklists/store', [ChecklistController::class, 'storeTemplate'])->name('admin.storeTemplate');
    Route::get('/checklists/fill', [ChecklistController::class, 'showForm'])->name('checklists.fill');
    Route::post('/checklists/submit', [ChecklistController::class, 'storeAnswer'])->name('checklists.submit');
    Route::get('/admin/checklists', [ChecklistController::class, 'indexTemplate'])->name('admin.indexTemplate');
    Route::get('/checklists', [ChecklistController::class, 'index'])->name('checklists.index');
    Route::get('/checklists/fill/{type}/{date}', [ChecklistController::class, 'create'])->name('checklists.create');
});

require __DIR__.'/auth.php';