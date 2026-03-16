<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Kantor & Waktu</h2>
            <p class="text-sm text-gray-500 mt-0.5">Atur koordinat, radius, dan kebijakan waktu absensi</p>
        </div>
    </x-slot>

    <style>
        .field-label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px; }
        .field-input { width: 100%; padding: 9px 12px; font-size: 14px; color: #111827; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; appearance: none; -webkit-appearance: none; }
        .field-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); background: #fff; }
        .btn-secondary { width: 100%; padding: 10px 16px; font-size: 13px; font-weight: 500; color: #374151; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
        .btn-secondary:hover { background: #f3f4f6; }
        .btn-primary { width: 100%; padding: 10px 16px; font-size: 13px; font-weight: 600; color: #fff; background: #2563eb; border: none; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
        .btn-primary:hover { background: #1d4ed8; }
        .stat-card { background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 10px; padding: 10px 14px; }
        .stat-label { font-size: 10px; font-weight: 600; color: #9ca3af; text-transform: uppercase; }
        .stat-value { font-size: 13px; font-weight: 500; color: #111827; margin-top: 3px; }
        .section-divider { font-size: 10px; font-weight: 800; color: #6366f1; text-transform: uppercase; letter-spacing: 0.1em; margin: 20px 0 10px 0; display: block; border-bottom: 1px solid #f3f4f6; padding-bottom: 5px; }
    </style>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Panel Kiri: Form --}}
                <div class="md:col-span-1 bg-white border border-gray-100 rounded-2xl shadow-sm p-6 flex flex-col">
                    <form action="{{ route('admin.presence.updateSettings') }}" method="POST" class="flex flex-col flex-1">
                        @csrf
                        
                        <span class="section-divider" style="margin-top: 0">Lokasi & Radius</span>
                        <div class="space-y-4">
                            <div>
                                <label for="latitude" class="field-label">Latitude</label>
                                <input id="latitude" name="latitude" type="text" class="field-input" 
                                       value="{{ $setting->latitude ?? '-6.200000' }}" required />
                            </div>

                            <div>
                                <label for="longitude" class="field-label">Longitude</label>
                                <input id="longitude" name="longitude" type="text" class="field-input" 
                                       value="{{ $setting->longitude ?? '106.816600' }}" required />
                            </div>

                            <div>
                                <label for="radius" class="field-label">Radius Absensi</label>
                                <div class="relative">
                                    <input id="radius" name="radius" type="number" class="field-input" style="padding-right: 36px;"
                                           value="{{ $setting->radius ?? '50' }}" required />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">m</span>
                                </div>
                            </div>
                        </div>

                        <span class="section-divider">Kebijakan Waktu</span>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Jam Masuk</label>
                                    <input name="check_in_time" type="time" class="field-input" 
                                           value="{{ substr($setting->check_in_time ?? '08:00', 0, 5) }}" required>
                                </div>
                                <div>
                                    <label class="field-label">Jam Pulang</label>
                                    <input name="check_out_time" type="time" class="field-input" 
                                           value="{{ substr($setting->check_out_time ?? '17:00', 0, 5) }}" required>
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Toleransi (Menit)</label>
                                <div class="relative">
                                    <input name="late_tolerance" type="number" class="field-input" 
                                           value="{{ $setting->late_tolerance ?? '15' }}" required />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">min</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-5 border-t border-gray-100 flex flex-col gap-2">
                            <button type="button" onclick="getLocation()" class="btn-secondary">Gunakan Lokasi Saya</button>
                            <button type="submit" class="btn-primary">Simpan Semua Pengaturan</button>
                        </div>
                    </form>
                </div>

                {{-- Panel Kanan: Peta & Stat --}}
                <div class="md:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <p class="field-label">Visualisasi Area</p>
                        <span class="text-xs font-medium text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live Map
                        </span>
                    </div>

                    <div id="map" class="rounded-xl border border-gray-100" style="height: 400px;"></div>

                    <div class="grid grid-cols-3 gap-2.5 mt-4">
                        <div class="stat-card">
                            <div class="stat-label">Koordinat</div>
                            <div class="stat-value text-[11px]" id="coords-disp">
                                <span id="lat-disp">{{ $setting->latitude ?? '-6.200000' }}</span>, 
                                <span id="lng-disp">{{ $setting->longitude ?? '106.816600' }}</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Shift Kerja</div>
                            <div class="stat-value">{{ substr($setting->check_in_time ?? '08:00', 0, 5) }} - {{ substr($setting->check_out_time ?? '17:00', 0, 5) }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Radius & Tol.</div>
                            <div class="stat-value"><span id="radius-disp">{{ $setting->radius ?? '50' }}</span>m / {{ $setting->late_tolerance ?? '15' }}m</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var initialLat = {{ $setting->latitude ?? -6.2000 }};
        var initialLng = {{ $setting->longitude ?? 106.8166 }};

        var map = L.map('map').setView([initialLat, initialLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

        function updateDisplay(lat, lng) {
            document.getElementById('lat-disp').textContent = parseFloat(lat).toFixed(6);
            document.getElementById('lng-disp').textContent = parseFloat(lng).toFixed(6);
        }

        function syncMapToInput(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            updateDisplay(lat, lng);
        }

        function syncInputToMap() {
            var lat = parseFloat(document.getElementById('latitude').value);
            var lng = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) {
                var newPos = new L.LatLng(lat, lng);
                marker.setLatLng(newPos);
                map.panTo(newPos);
                updateDisplay(lat, lng);
            }
        }

        marker.on('dragend', function () {
            var p = marker.getLatLng();
            syncMapToInput(p.lat, p.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng([e.latlng.lat, e.latlng.lng]);
            syncMapToInput(e.latlng.lat, e.latlng.lng);
        });

        document.getElementById('latitude').addEventListener('input', syncInputToMap);
        document.getElementById('longitude').addEventListener('input', syncInputToMap);

        document.getElementById('radius').addEventListener('input', function () {
            document.getElementById('radius-disp').textContent = (this.value || '0');
        });

        function getLocation() {
            if (!navigator.geolocation) return alert('Browser tidak mendukung GPS.');
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                map.setView([lat, lng], 17);
                marker.setLatLng([lat, lng]);
                syncMapToInput(lat, lng);
            });
        }
    </script>
</x-app-layout>