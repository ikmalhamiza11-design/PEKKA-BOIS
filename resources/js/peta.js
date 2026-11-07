const wadah = document.getElementById('peta');

if (wadah) {
    const titik = JSON.parse(wadah.dataset.titik || '[]');

    const muatSkrip = (alamat) =>
        new Promise((berhasil, gagal) => {
            const skrip = document.createElement('script');
            skrip.src = alamat;
            skrip.onload = berhasil;
            skrip.onerror = gagal;
            document.head.appendChild(skrip);
        });

    const gaya = document.createElement('link');
    gaya.rel = 'stylesheet';
    gaya.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
    document.head.appendChild(gaya);

    muatSkrip('https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js')
        .then(() => {
            const L = window.L;
            const peta = L.map(wadah, { scrollWheelZoom: false }).setView([-6.55, 106.8], 10);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; kontributor OpenStreetMap',
            }).addTo(peta);

            const batas = [];

            titik.forEach((item) => {
                const isi = document.createElement('div');

                const nama = document.createElement('strong');
                nama.textContent = item.nama;

                const wilayah = document.createElement('div');
                wilayah.textContent = item.wilayah;

                const toko = document.createElement('div');
                toko.textContent = item.toko + ' toko';

                const tautan = document.createElement('a');
                tautan.href = item.url;
                tautan.textContent = 'Lihat produk';

                isi.append(nama, wilayah, toko, tautan);

                L.circleMarker([item.lat, item.lng], {
                    radius: 11,
                    color: '#48550F',
                    weight: 2,
                    fillColor: '#B0BC13',
                    fillOpacity: 0.85,
                })
                    .addTo(peta)
                    .bindPopup(isi);

                batas.push([item.lat, item.lng]);
            });

            if (batas.length > 1) {
                peta.fitBounds(batas, { padding: [40, 40], maxZoom: 13 });
            } else if (batas.length === 1) {
                peta.setView(batas[0], 12);
            }
        })
        .catch(() => {
            wadah.textContent = 'Peta tidak bisa dimuat. Periksa sambungan internetmu.';
            wadah.classList.add('flex', 'items-center', 'justify-center', 'p-6', 'text-center', 'text-sm');
        });
}
