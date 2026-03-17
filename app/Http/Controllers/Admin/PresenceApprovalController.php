<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
use App\Models\OfficeSetting;

class PresenceApprovalController extends Controller
{
    /**
     * 1. Halaman Utama Approval Absensi (Hanya menampilkan kategori 'masuk')
     */
    public function index(Request $request)
    {
        $status = $request->query('status'); 
        $query = Presence::query()->with(['user'])->where('category', 'masuk'); 

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('is_approved', $status);
        }

        $presences = $query->orderBy('date', 'desc')->get();
        
        return view('admin.attendance.approval', compact('presences', 'status'));
    }

    /**
     * 2. Sub-menu: Izin & Cuti (Menampilkan kategori selain 'masuk')
     * Menampilkan ke halaman resources/views/admin/attendance/perizinan.blade.php
     */
// app/Http/Controllers/Admin/PresenceApprovalController.php

public function perizinan()
{
    // Mengambil data selain kategori 'masuk' yang ada di tabel presences
    $permissions = Presence::with('user')
        ->whereIn('category', ['cuti', 'sakit', 'izin'])
        ->orderBy('date', 'desc')
        ->get();

    return view('admin.attendance.perizinan', compact('permissions'));
}

public function approve($id)
{
    Presence::findOrFail($id)->update(['is_approved' => 'approved']);
    return back()->with('success', 'Pengajuan telah disetujui.');
}

public function reject($id)
{
    Presence::findOrFail($id)->update(['is_approved' => 'rejected']);
    return back()->with('success', 'Pengajuan telah ditolak.');
}

    /**
     * Update Status (Universal untuk Absen, Cuti, Izin, Sakit)
     */
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