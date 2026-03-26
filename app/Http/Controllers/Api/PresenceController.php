<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Leave;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresenceController extends Controller
{
    /**
     * 1. Check-In (Masuk ke Tabel Presences)
     */
    public function storeCheckIn(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        // Cek apakah hari ini sudah absen masuk
        $alreadyCheckedIn = Presence::where('user_id', $user->id)
            ->where('date', $today)
            ->where('category', 'masuk')
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk hari ini.'], 422);
        }

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('presence_photos', 'public');
        }

        $presence = Presence::create([
            'user_id'     => $user->id,
            'date'        => $today,
            'category'    => 'masuk', // Kategori wajib agar terpisah dari izin
            'check_in'    => now()->format('H:i:s'),
            'photo_in'    => $path,
            'lat_in'      => $request->latitude,
            'lng_in'      => $request->longitude,
            'notes'       => $request->notes ?? 'Absen Masuk Mobile',
            'is_approved' => 'pending'
        ]);

        return response()->json(['success' => true, 'message' => 'Check-in berhasil!', 'data' => $presence], 201);
    }

    /**
     * 2. Check-Out (Update Tabel Presences)
     */
    public function storeCheckOut(Request $request)
    {
        $user = $request->user();
        $presence = Presence::where('user_id', $user->id)
                            ->where('date', now()->format('Y-m-d'))
                            ->where('category', 'masuk')
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
            return response()->json(['success' => true, 'message' => 'Check-out berhasil!', 'data' => $presence], 200);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database.'], 500);
    }

    /**
     * 3. Izin/Sakit/Cuti (Masuk ke Tabel Leaves)
     */
    public function storePermission(Request $request)
    {
        $user = $request->user();
        $startDate = $request->start_date;

        // Cek apakah sudah absen masuk di tabel presences pada tanggal yang diajukan
        $alreadyPresent = Presence::where('user_id', $user->id)
            ->whereDate('date', $startDate)
            ->where('category', 'masuk')
            ->exists();

        if ($alreadyPresent) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda sudah memiliki catatan absen masuk pada tanggal ' . $startDate
            ], 422);
        }

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('leaves', 'public');
        }

        // SIMPAN KE TABEL LEAVES
        $leave = Leave::create([
            'user_id'         => $user->id,
            'type'            => strtolower($request->category), // sakit, cuti, atau izin
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date ?? $request->start_date,
            'reason'          => $request->notes, // Flutter kirim 'notes'
            'attachment_file' => $attachment, 
            'status'          => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Laporan berhasil terkirim ke tabel leaves'], 201);
    }

    public function todayStatus(Request $request)
{
    $user = $request->user();
    $today = now()->format('Y-m-d');

    $presence = Presence::where('user_id', $user->id)
        ->where('date', $today)
        ->where('category', 'masuk')
        ->first();

    return response()->json([
        'has_checkin'  => $presence !== null,
        'has_checkout' => $presence?->check_out !== null,
        'check_in'     => $presence?->check_in,
        'check_out'    => $presence?->check_out,
    ]);
}
}