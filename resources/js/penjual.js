function pasangPratinjauFoto() {
    const input = document.querySelector('[data-foto-input]');
    const pratinjau = document.querySelector('[data-foto-pratinjau]');

    if (!input || !pratinjau) {
        return;
    }

    const gambar = pratinjau.querySelector('img');
    const info = document.querySelector('[data-foto-info]');
    let alamat = null;

    input.addEventListener('change', () => {
        if (alamat) {
            URL.revokeObjectURL(alamat);
            alamat = null;
        }

        const berkas = input.files && input.files[0];

        if (!berkas) {
            pratinjau.classList.add('hidden');

            return;
        }

        alamat = URL.createObjectURL(berkas);
        gambar.src = alamat;
        pratinjau.classList.remove('hidden');

        if (info) {
            info.textContent = berkas.name + ' (' + (berkas.size / 1048576).toFixed(1) + ' MB)';
        }
    });
}

document.addEventListener('DOMContentLoaded', pasangPratinjauFoto);
