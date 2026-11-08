const labelKekuatan = ['Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat kuat'];

function cekAturan(nilai) {
    return {
        panjang: nilai.length >= 8,
        kapital: /[a-z]/.test(nilai) && /[A-Z]/.test(nilai),
        angka: /\d/.test(nilai),
        simbol: /[^A-Za-z0-9]/.test(nilai),
    };
}

function pasangTombolLihat(tombol) {
    const input = document.getElementById(tombol.dataset.togglePassword);
    if (!input || tombol.dataset.terpasang) return;
    tombol.dataset.terpasang = '1';

    const ikonLihat = tombol.querySelector('[data-ikon-lihat]');
    const ikonSembunyi = tombol.querySelector('[data-ikon-sembunyi]');

    tombol.addEventListener('pointerdown', (e) => e.preventDefault());
    tombol.addEventListener('mousedown', (e) => e.preventDefault());

    tombol.addEventListener('click', (e) => {
        e.preventDefault();
        const awal = input.selectionStart;
        const akhir = input.selectionEnd;
        const tampil = input.type === 'password';

        input.type = tampil ? 'text' : 'password';
        tombol.setAttribute('aria-pressed', String(tampil));
        tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        if (ikonLihat) ikonLihat.classList.toggle('hidden', tampil);
        if (ikonSembunyi) ikonSembunyi.classList.toggle('hidden', ! tampil);

        if (document.activeElement === input && awal !== null) {
            input.setSelectionRange(awal, akhir);
        }
    });
}

function pasangMeter(meter) {
    const input = document.getElementById(meter.dataset.strengthFor);
    if (!input) return;

    const batang = meter.querySelectorAll('[data-bar]');
    const label = meter.querySelector('[data-strength-label]');
    const daftar = meter.querySelectorAll('[data-rule]');

    const perbarui = () => {
        const hasil = cekAturan(input.value);
        const jumlah = Object.values(hasil).filter(Boolean).length;

        batang.forEach((el, i) => {
            el.classList.toggle('bg-olive', i < jumlah);
            el.classList.toggle('bg-olive/15', i >= jumlah);
        });

        label.textContent = input.value ? labelKekuatan[jumlah] : '-';
        daftar.forEach((el) => {
            el.dataset.ok = String(hasil[el.dataset.rule]);
        });
    };

    input.addEventListener('input', perbarui);
    perbarui();
}

function mulai() {
    document.querySelectorAll('[data-toggle-password]').forEach(pasangTombolLihat);
    document.querySelectorAll('[data-strength-for]').forEach(pasangMeter);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}
