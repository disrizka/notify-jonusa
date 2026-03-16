<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Leave; 
use Illuminate\Http\Request;
use App\Models\OfficeSetting;

class PresenceApprovalController extends Controller
{
    /**
     * 1. Halaman Utama Approval Absensi
     */
    public function index(Request $request)
    {
        $status = $request->query('status'); 

        // Menggunakan query() agar build query lebih bersih
        $query = Presence::query()->with(['user']); 

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('is_approved', $status);
        }

        $presences = $query->orderBy('date', 'desc')
                           ->orderBy('check_in', 'desc')
                           ->get();
        
        return view('admin.attendance.approval', compact('presences', 'status'));
    }

    /**
     * Update Status Absen (Setuju/Tolak)
     */
    public function updateStatus(Request $request, $id, $status)
    {
        $presence = Presence::findOrFail($id);
        
        // Pastikan status yang dikirim valid
        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            return back()->with('error', 'Status tidak valid!');
        }

        $presence->update([
            'is_approved' => $status
        ]);

        return back()->with('success', 'Status absensi berhasil diperbarui!');
    }

    /**
     * 2. Sub-menu: Izin & Cuti
     */
    public function leaveIndex()
    {
        $leaves = Leave::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.attendance.leaves', compact('leaves'));
    }

    public function leaveApprove($id) {
        Leave::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', 'Izin/Cuti berhasil disetujui');
    }

    public function leaveReject($id) {
        Leave::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Izin/Cuti telah ditolak');
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

        return back()->with('success', 'Lokasi dan Radius Kantor berhasil diperbarui!');
    }

    /**
     * API untuk Mobile (Check-in/Out)
     */
    public function getConfig() {
        $setting = OfficeSetting::first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'latitude'  => (float)($setting->latitude ?? -6.2000),
                'longitude' => (float)($setting->longitude ?? 106.8166),
                'radius'    => (int)($setting->radius ?? 50),
            ]
        ]);
    }
}