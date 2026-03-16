<?php

namespace App\Http\Controllers\Admin; // Sesuaikan namespace ke folder Admin

use App\Http\Controllers\Controller;
use App\Models\OfficeSetting; // Pastikan Model sudah dibuat
use Illuminate\Http\Request;

class OfficeSettingController extends Controller
{
   public function index()
    {
        // Mengambil satu data pengaturan kantor
        $setting = OfficeSetting::first();
        
        // Memanggil file di resources/views/admin/presence/settings.blade.php
       return view('admin.attendance.settings', compact('setting'));
    }

   public function update(Request $request)
{
    $request->validate([
        'latitude' => 'required',
        'longitude' => 'required',
        'radius' => 'required|numeric',
        'check_in_time' => 'required',
        'check_out_time' => 'required',
        'late_tolerance' => 'required|numeric',
    ]);

    // ANTI-BUG: Jika data pertama tidak ada, Laravel akan otomatis membuatnya
    // Kita gunakan ID 1 sebagai patokan data pengaturan tunggal
    OfficeSetting::updateOrCreate(
        ['id' => 1], 
        [
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'radius'         => $request->radius,
            'check_in_time'  => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'late_tolerance' => $request->late_tolerance,
        ]
    );

    return back()->with('success', 'Pengaturan kantor dan waktu berhasil diperbarui!');
}
}