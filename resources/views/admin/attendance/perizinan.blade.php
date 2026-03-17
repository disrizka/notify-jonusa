<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Izin & Cuti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Notifikasi --}}
            @if(session('success'))
                <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded shadow-sm flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xl">×</button>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    
                    {{-- Judul & Informasi --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Pengajuan</h3>
                        <p class="text-sm text-gray-500">Menampilkan permohonan Cuti, Izin, dan Sakit karyawan yang memerlukan persetujuan.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Alasan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bukti</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($permissions as $p)
                                    <tr class="hover:bg-gray-50 transition">
                                        {{-- Karyawan --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs uppercase">
                                                    {{ substr($p->user->name, 0, 2) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-bold text-gray-900">{{ $p->user->name }}</div>
                                                    <div class="text-[10px] text-gray-500">{{ $p->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Tipe Badge --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $colors = [
                                                    'cuti' => 'bg-orange-100 text-orange-700',
                                                    'sakit' => 'bg-rose-100 text-rose-700',
                                                    'izin' => 'bg-blue-100 text-blue-700',
                                                ];
                                                $color = $colors[$p->category] ?? 'bg-gray-100 text-gray-700';
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase {{ $color }}">
                                                {{ $p->category }}
                                            </span>
                                        </td>

                                        {{-- Tanggal --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 font-medium">
                                            {{ \Carbon\Carbon::parse($p->date)->format('d M Y') }}
                                        </td>

                                        {{-- Alasan --}}
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div class="max-w-xs truncate" title="{{ $p->notes }}">
                                                {{ $p->notes ?? '-' }}
                                            </div>
                                        </td>

                                        {{-- Bukti/Lampiran --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                                            @if($p->attachment)
                                                <a href="{{ asset('storage/'.$p->attachment) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-bold flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    Lihat Dokumen
                                                </a>
                                            @else
                                                <span class="text-gray-400 italic">Tidak ada</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 font-black uppercase tracking-wider 
                                                {{ $p->is_approved == 'approved' ? 'bg-emerald-100 text-emerald-700' : ($p->is_approved == 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $p->is_approved }}
                                            </span>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            @if($p->is_approved == 'pending')
                                                <div class="flex justify-center gap-2">
                                                    {{-- Tombol Terima --}}
                                                    <form action="{{ route('admin.presence.approve', $p->id) }}" method="POST" onsubmit="return confirm('Setujui pengajuan ini?')">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="p-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full shadow-sm transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                        </button>
                                                    </form>

                                                    {{-- Tombol Tolak --}}
                                                    <form action="{{ route('admin.presence.reject', $p->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan ini?')">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="p-2 bg-rose-500 hover:bg-rose-600 text-white rounded-full shadow-sm transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-300 italic text-[10px]">Sudah diproses</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <p class="font-bold">Tidak ada pengajuan perizinan masuk.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>