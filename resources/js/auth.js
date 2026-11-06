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
    if (!input) return;

    tombol.addEventListener('click', () => {
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        tombol.setAttribute('aria-pressed', String(tampil));
        tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
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

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-toggle-password]').forEach(pasangTombolLihat);
    document.querySelectorAll('[data-strength-for]').forEach(pasangMeter);
});
