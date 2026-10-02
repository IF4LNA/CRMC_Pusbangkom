{{--
    Peta lokasi kantor PUSBANGKOM. Memakai Leaflet + OpenStreetMap (gratis,
    tanpa API key). Koordinat dikirim dari HomeController sebagai derajat
    desimal supaya tidak ada konversi DMS yang terduplikasi di view.

    Assets Leaflet dimuat lewat @push('styles') dan @push('scripts') supaya
    tidak membebani halaman lain.
--}}
<section id="peta" class="scroll-mt-20 py-10 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-2xl mb-6">
            <span class="eyebrow mb-2 block">Lokasi</span>
            <h2 class="section-title">Kantor Pusat PUSBANGKOM</h2>
            <p class="page-sub mt-2 leading-relaxed">
                Peta interaktif menampilkan posisi kantor pusat beserta lingkungan
                sekitarnya. Geser atau perbesar peta untuk melihat jalan
                penghubung ke lokasi.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

            {{-- Panel informasi --}}
            <div class="card p-4 flex flex-col">
                <h3 class="card-title mb-3">Informasi Lokasi</h3>

                <ul class="space-y-3 text-xs flex-1">
                    <li class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">Instansi</p>
                            <p class="page-sub leading-relaxed mt-0.5">
                                Pusat Sumber Daya Air dan Prasarana Wilayah III Bandung
                            </p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="map-pinned" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">Alamat</p>
                            <p class="page-sub leading-relaxed mt-0.5">
                                Jl. Padjajaran, Bandung, Jawa Barat
                            </p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Koordinat</p>
                            {{-- $koordinatDms sudah berupa entitas HTML, jadi
                                 dicetak apa adanya tanpa escaping. --}}
                            <p class="page-sub mt-0.5 font-mono text-[11px] break-all">
                                {!! $koordinatDms !!}
                            </p>
                            <p class="text-slate-400 font-mono text-[10px] mt-0.5">
                                {{ $latitude }}, {{ $longitude }}
                            </p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">Telepon</p>
                            <p class="page-sub mt-0.5">(022) 4203113</p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Email</p>
                            <a href="mailto:pusbangkom@pu.go.id"
                               class="text-blue-700 hover:text-blue-900 hover:underline break-all">
                                pusbangkom@pu.go.id
                            </a>
                        </div>
                    </li>
                </ul>

                <a href="{{ $tautanPeta }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-4">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Buka di Google Maps
                </a>
            </div>

            {{-- Peta --}}
            <div class="lg:col-span-2">
                <div class="card overflow-hidden h-full flex flex-col">
                    {{-- id dipakai oleh JS di bawah untuk menginisialisasi Leaflet. --}}
                    <div id="petaCrmc"
                         class="w-full flex-1 min-h-[320px] md:min-h-[480px] bg-slate-200"
                         data-lat="{{ $latitude }}"
                         data-lng="{{ $longitude }}"
                         data-nama="PUSBANGKOM ACP"></div>

                    <div class="px-4 py-2.5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            Peta dasar dari OpenStreetMap
                        </p>
                        <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer"
                           class="text-[10px] text-slate-400 hover:text-slate-600 transition">
                            &copy; Kontributor OpenStreetMap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // ===================== PETA LOKASI (LEAFLET) =====================
    // Inisialisasi setelah window.onload yang sudah ada di layout selesai.
    window.addEventListener('load', function () {
        const el = document.getElementById('petaCrmc');
        if (!el || typeof L === 'undefined') return;

        const lat = parseFloat(el.dataset.lat);
        const lng = parseFloat(el.dataset.lng);
        const nama = el.dataset.nama || 'Lokasi CRMC';

        const peta = L.map(el, { scrollWheelZoom: false }).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; Kontributor OpenStreetMap',
        }).addTo(peta);

        // Marker bawaan Leaflet memakai path gambar relatif yang rusak saat
        // dimuat dari CDN, jadi diganti divIcon berisi SVG seukuran pin.
        const pinSvg = [
            '<svg viewBox="0 0 24 24" width="34" height="34" fill="none"',
            '     xmlns="http://www.w3.org/2000/svg">',
            '  <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"',
            '        fill="#2563eb" stroke="#1e293b" stroke-width="1.5"/>',
            '  <circle cx="12" cy="9" r="2.8" fill="#0f172a"/>',
            '</svg>',
        ].join('');

        const marker = L.marker([lat, lng], {
            icon: L.divIcon({
                html: pinSvg,
                className: '',
                iconSize: [34, 34],
                iconAnchor: [17, 34],
                popupAnchor: [0, -32],
            }),
            title: nama,
            alt: nama,
        }).addTo(peta);

        marker.bindPopup(
            '<div style="font-family:inherit;min-width:180px">' +
            '<strong style="display:block;font-size:13px;color:#0f172a">' + nama + '</strong>' +
            '<span style="display:block;font-size:11px;color:#64748b;margin-top:4px">' +
            'Pusat Sumber Daya Air dan Prasarana Wilayah III Bandung</span>' +
            '<span style="display:block;font-size:10px;color:#94a3b8;margin-top:4px;font-family:monospace">' +
            lat + ', ' + lng + '</span>' +
            '</div>'
        );

        // Zoom dengan roda mouse baru aktif setelah diklik, supaya halaman
        // tidak ikut ter-scroll saat kursor berada di atas peta.
        peta.on('click', function () { peta.scrollWheelZoom.enable(); });
        peta.on('mouseout', function () { peta.scrollWheelZoom.disable(); });

        // Ukuran ulang setelah layout selesai, karena tinggi elemen bisa
        // masih berubah saat font web selesai dimuat.
        setTimeout(function () { peta.invalidateSize(); }, 300);
    });
</script>
@endpush
