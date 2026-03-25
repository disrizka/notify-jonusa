<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
use App\Models\OfficeSetting;
use App\Models\Leave;

class PresenceApprovalController extends Controller
{
    /**
     * 1. Halaman Utama Approval Absensi (Hanya menampilkan kategori 'masuk')
     */
  // Menampilkan Approval Absensi Biasa (Menu 1)
// app/Http/Controllers/Admin/PresenceApprovalController.php

public function index(Request $request)
{
    $status = $request->query('status'); 
    
    // FILTER KETAT: Hanya ambil yang category-nya 'masuk'
    // Ini agar data Sakit/Cuti TIDAK MUNCUL di approval.blade.php
    $query = Presence::query()
        ->with(['user'])
        ->where('category', 'masuk'); 

    if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
        $query->where('is_approved', $status);
    }

    $presences = $query->orderBy('date', 'desc')->get();
    return view('admin.attendance.approval', compact('presences', 'status'));
}

    public function perizinan()
    {
       
        $permissions = Leave::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.attendance.perizinan', compact('permissions'));
    }

  
    public function leaveApprove($id)
    {
        Leave::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', 'Pengajuan izin berhasil disetujui.');
    }

    public function leaveReject($id)
    {
        Leave::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Pengajuan izin ditolak.');
    }
    public function updateStatus(Request $request, $id, $status)
    {
        $presence = Presence::findOrFail($id);
        
        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            return back()->with('error', 'Status tidak valid!');
        }

        $presence->update(['is_approved' => $status]);

        return back()->with('success', 'Status berhasil diperbarui!');
    }

    /**
     * 3. Sub-menu: Jadwal Kerja
     */
    public function schedule()
    {
        return view('admin.attendance.schedule');
    }

    /**
     * 4. Sub-menu: Riwayat Presensi
     */
    public function history()
    {
        $history = Presence::with('user')
            ->where('is_approved', 'approved')
            ->orderBy('date', 'desc')
            ->get();
            
        return view('admin.attendance.history', compact('history'));
    }

    /**
     * 5. Settings Absensi
     */
    public function settings() {
        $setting = OfficeSetting::first() ?? new OfficeSetting();
        return view('admin.attendance.settings', compact('setting'));
    }

    public function updateSettings(Request $request) {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required|numeric',
        ]);

        OfficeSetting::updateOrCreate(
            ['id' => 1], 
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
            ]
        );

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}