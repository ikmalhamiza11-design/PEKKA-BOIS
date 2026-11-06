function pasangDialog() {
    document.querySelectorAll('[data-buka-dialog]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            const dialog = document.getElementById(tombol.dataset.bukaDialog);

            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
            }
        });
    });

    document.querySelectorAll('[data-tutup-dialog]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            const dialog = tombol.closest('dialog');

            if (dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) {
                dialog.close();
            }
        });
    });
}

function pasangKirimOtomatis() {
    document.querySelectorAll('[data-kirim-otomatis]').forEach((elemen) => {
        elemen.addEventListener('change', () => {
            if (elemen.form) {
                elemen.form.submit();
            }
        });
    });
}

function pasangStepper() {
    document.querySelectorAll('[data-stepper]').forEach((blok) => {
        const input = blok.querySelector('[data-stepper-nilai]');
        const kurang = blok.querySelector('[data-stepper-kurang]');
        const tambah = blok.querySelector('[data-stepper-tambah]');

        if (!input || input.disabled) return;

        const maks = Number(blok.dataset.maks) || 1;
        const harga = Number(blok.dataset.harga) || 0;

        const perbarui = () => {
            let nilai = parseInt(input.value, 10);

            if (Number.isNaN(nilai) || nilai < 1) nilai = 1;
            if (nilai > maks) nilai = maks;

            input.value = nilai;
            kurang.disabled = nilai <= 1;
            tambah.disabled = nilai >= maks;

            const total = 'Rp ' + (nilai * harga).toLocaleString('id-ID');
            document.querySelectorAll('[data-subtotal]').forEach((el) => {
                el.textContent = total;
            });
            document.querySelectorAll('[data-jumlah-item]').forEach((el) => {
                el.textContent = nilai + ' item';
            });
        };

        kurang.addEventListener('click', () => {
            input.value = Number(input.value) - 1;
            perbarui();
        });

        tambah.addEventListener('click', () => {
            input.value = Number(input.value) + 1;
            perbarui();
        });

        input.addEventListener('change', perbarui);
        perbarui();
    });
}

function pasangTab() {
    document.querySelectorAll('[data-tab-grup]').forEach((grup) => {
        const tombol = grup.querySelectorAll('[data-tab-tombol]');
        const panel = grup.querySelectorAll('[data-tab-panel]');

        const pilih = (nama) => {
            tombol.forEach((item) => {
                const aktif = item.dataset.tabTombol === nama;
                item.setAttribute('aria-selected', String(aktif));
                item.classList.toggle('bg-olive', aktif);
                item.classList.toggle('text-white', aktif);
                item.classList.toggle('text-olive', !aktif);
            });

            panel.forEach((item) => {
                item.hidden = item.dataset.tabPanel !== nama;
            });
        };

        tombol.forEach((item) => {
            item.addEventListener('click', () => pilih(item.dataset.tabTombol));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    pasangDialog();
    pasangKirimOtomatis();
    pasangStepper();
    pasangTab();
});
