let dialog = null;
let aksi = null;

function siapkanDialog() {
    if (dialog) return dialog;

    dialog = document.createElement('dialog');
    dialog.setAttribute('aria-labelledby', 'konfirmasi-judul');
    dialog.className = 'm-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl border border-olive/10 bg-paper p-6 text-ink shadow-xl backdrop:bg-black/50';
    dialog.innerHTML = `
        <h2 id="konfirmasi-judul" class="text-lg font-extrabold"></h2>
        <p id="konfirmasi-isi" class="mt-2 text-sm text-ink/70"></p>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" data-konfirmasi-batal class="btn-secondary sm:!w-auto sm:px-5">Tidak</button>
            <button type="button" data-konfirmasi-ya class="btn-primary sm:!w-auto sm:px-5">Ya, lanjutkan</button>
        </div>
    `;
    document.body.appendChild(dialog);

    dialog.querySelector('[data-konfirmasi-batal]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-konfirmasi-ya]').addEventListener('click', () => {
        const lanjut = aksi;
        aksi = null;
        dialog.close();
        if (lanjut) lanjut();
    });
    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) dialog.close();
    });
    dialog.addEventListener('close', () => {
        aksi = null;
    });

    return dialog;
}

function tanya(form, lanjut) {
    const kotak = siapkanDialog();

    kotak.querySelector('#konfirmasi-judul').textContent = form.dataset.konfirmasiJudul || 'Anda yakin?';
    kotak.querySelector('#konfirmasi-isi').textContent = form.dataset.konfirmasi;
    kotak.querySelector('[data-konfirmasi-ya]').textContent = form.dataset.konfirmasiTombol || 'Ya, lanjutkan';
    aksi = lanjut;

    if (typeof kotak.showModal === 'function') {
        kotak.showModal();
    } else if (window.confirm(form.dataset.konfirmasi)) {
        lanjut();
    }
}

document.addEventListener('submit', (e) => {
    const form = e.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.konfirmasi || form.dataset.terkonfirmasi) {
        return;
    }

    e.preventDefault();

    const pengirim = e.submitter || undefined;

    tanya(form, () => {
        form.dataset.terkonfirmasi = '1';
        form.requestSubmit(pengirim);
    });
});
