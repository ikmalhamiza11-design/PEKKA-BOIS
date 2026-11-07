const akar = document.getElementById('sahabat');

if (akar) {
    const percakapan = document.getElementById('sahabat-percakapan');
    const form = document.getElementById('sahabat-form');
    const kolom = document.getElementById('sahabat-pesan');
    const tombol = document.getElementById('sahabat-kirim');
    const saran = document.getElementById('sahabat-saran');
    const riwayat = [];
    let sibuk = false;

    const gulir = () => {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    };

    const gelembung = (teks, dariPengguna) => {
        const elemen = document.createElement('div');
        elemen.className = dariPengguna
            ? 'max-w-[85%] self-end whitespace-pre-line rounded-2xl rounded-tr-sm bg-olive px-4 py-3 text-sm text-white'
            : 'max-w-[85%] self-start whitespace-pre-line rounded-2xl rounded-tl-sm bg-lime/25 px-4 py-3 text-sm';
        elemen.textContent = teks;
        percakapan.appendChild(elemen);
        gulir();

        return elemen;
    };

    const tautanProduk = (daftar) => {
        if (!daftar || daftar.length === 0) {
            return;
        }

        const wadah = document.createElement('div');
        wadah.className = 'flex max-w-[85%] flex-col gap-2 self-start';

        daftar.forEach((item) => {
            const tautan = document.createElement('a');
            tautan.href = item.url;
            tautan.className = 'rounded-xl border border-olive/20 bg-white px-3 py-2 text-sm hover:bg-lime/20';

            const nama = document.createElement('span');
            nama.className = 'block font-bold text-olive';
            nama.textContent = item.nama;

            const info = document.createElement('span');
            info.className = 'block text-xs text-ink/70';
            info.textContent = item.harga + ' · ' + item.toko;

            tautan.append(nama, info);
            wadah.appendChild(tautan);
        });

        percakapan.appendChild(wadah);
        gulir();
    };

    const kirim = async (teks) => {
        const pesan = teks.trim();

        if (pesan === '' || sibuk) {
            return;
        }

        sibuk = true;
        tombol.disabled = true;
        saran.classList.add('hidden');
        gelembung(pesan, true);
        kolom.value = '';

        const menunggu = gelembung('Sahabat PEKKA sedang mengetik...', false);

        try {
            const respons = await fetch(akar.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': akar.dataset.token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ pesan, riwayat: riwayat.slice(-10) }),
            });

            const data = await respons.json().catch(() => ({}));

            if (!respons.ok) {
                let galat = data.galat || 'Maaf, terjadi gangguan. Coba lagi sebentar.';

                if (respons.status === 429 && !data.galat) {
                    galat = 'Terlalu banyak pertanyaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.';
                }

                if (respons.status === 419) {
                    galat = 'Halaman sudah terlalu lama dibuka. Muat ulang halaman lalu coba lagi.';
                }

                menunggu.textContent = galat;

                return;
            }

            menunggu.textContent = data.jawaban;
            riwayat.push({ peran: 'user', isi: pesan }, { peran: 'model', isi: data.jawaban });
            tautanProduk(data.produk);
        } catch (galat) {
            menunggu.textContent = 'Sambungan terputus. Periksa internetmu lalu coba lagi.';
        } finally {
            sibuk = false;
            tombol.disabled = false;
            kolom.focus();
            gulir();
        }
    };

    form.addEventListener('submit', (peristiwa) => {
        peristiwa.preventDefault();
        kirim(kolom.value);
    });

    kolom.addEventListener('keydown', (peristiwa) => {
        if (peristiwa.key === 'Enter' && !peristiwa.shiftKey) {
            peristiwa.preventDefault();
            kirim(kolom.value);
        }
    });

    saran.querySelectorAll('[data-saran]').forEach((item) => {
        item.addEventListener('click', () => kirim(item.dataset.saran));
    });

    if (akar.dataset.tanya) {
        kirim(akar.dataset.tanya);
    }
}
