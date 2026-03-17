<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\OfficeSetting; // Tambahkan ini
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PresenceController extends Controller
{
    public function storeCheckIn(Request $request)
    {
        $request->validate([
            'latitude'  => 'required',
            'longitude' => 'required',
            'photo'     => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes'     => 'nullable|string',
        ]);

        $user = $request->user();
        $today = now()->format('Y-m-d');
        $now = now(); // Jam server saat ini
        // --- LOGIKA VALIDASI WAKTU & TOLERANSI ---
        $setting = OfficeSetting::first();
        if ($setting) {
            // Gabungkan tanggal hari ini dengan jam batas dari setting
            $checkInLimit = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $setting->check_in_time);
            // Tambahkan toleransi (menit)
            $maxTime = $checkInLimit->addMinutes($setting->late_tolerance);

            if ($now->greaterThan($maxTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal! Sudah melewati batas waktu masuk (' . $maxTime->format('H:i') . ').'
                ], 422);
            }
        }

        // Cek double check-in
        $alreadyCheckedIn = Presence::where('user_id', $user->id)
                                    ->where('date', $today)
                                    ->exists();

        if ($alreadyCheckedIn) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan Check-in hari ini.'
            ], 400);
        }

        // Simpan foto
        $path = $request->file('photo')->store('presence_photos', 'public');

        $presence = Presence::create([
            'user_id'     => $user->id,
            'date'        => $today,
            'check_in'    => $now->format('H:i:s'),
            'lat_in'      => $request->latitude,
            'lng_in'      => $request->longitude,
            'photo_in'    => $path,
            'notes'       => $request->notes ?? 'Absen Masuk Mobile',
            'is_approved' => 'pending', 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil dikirim.',
            'data'    => $presence
        ], 201);
    }

    public function storeCheckOut(Request $request)
    {
        $user = $request->user();
        $presence = Presence::where('user_id', $user->id)
                            ->where('date', now()->format('Y-m-d')) // Pastikan checkout di hari yang sama
                            ->latest('id')
                            ->first();

        if (!$presence) {
            return response()->json(['success' => false, 'message' => 'Data masuk tidak ditemukan hari ini.'], 404);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('presence_photos', 'public');
            $presence->photo_out = $path;
        }

        $presence->check_out = now()->format('H:i:s');
        $presence->lat_out   = $request->latitude;
        $presence->lng_out   = $request->longitude;
        $presence->notes_out = $request->notes ?? 'Absen Pulang';

        if ($presence->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil tersimpan!',
                'data'    => $presence 
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database.'], 500);
    }

    // app/Http/Controllers/Api/PresenceController.php

public function storePermission(Request $request)
{
    $user = $request->user();
    // Gunakan 'date' sebagai field utama sesuai kiriman Flutter terbaru
    $targetDate = $request->date; 

    // 1. Logika Kunci Harian: Jika sudah absen 'masuk', tidak bisa izin di hari yang sama
    $alreadyPresent = Presence::where('user_id', $user->id)
        ->whereDate('date', $targetDate)
        ->where('category', 'masuk')
        ->exists();

    if ($alreadyPresent) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal! Anda sudah memiliki catatan absen masuk hari ini.'
        ], 422);
    }

    // 2. Simpan Lampiran
    $path = null;
    if ($request->hasFile('attachment')) {
        $path = $request->file('attachment')->store('permissions', 'public');
    }

    // 3. Simpan ke tabel presences (Agar sinkron dengan Dashboard Web)
    $presence = Presence::create([
        'user_id'     => $user->id,
        'category'    => strtolower($request->category), // 'sakit', 'cuti', 'izin'
        'date'        => $targetDate,
        'notes'       => $request->notes, // Penting: field 'notes' agar muncul di kolom Alasan di Web
        'attachment'  => $path,
        'is_approved' => 'pending'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Laporan berhasil dikirim'
    ], 201);
}
}