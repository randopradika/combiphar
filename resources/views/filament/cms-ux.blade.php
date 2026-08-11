{{--
    Dua perbaikan yang berlaku untuk SELURUH formulir di panel, dipasang sekali
    lewat render hook alih-alih disalin ke tiap halaman.

    1. Tombol Simpan menempel di bawah layar. Pada formulir terpanjang -- record
       Halaman punya sekitar 60 bidang -- Simpan berada di ujung bawah, jadi
       menyimpan satu perubahan kecil berarti menggulir melewati seluruh
       formulir lebih dulu.

    2. Peringatan saat meninggalkan formulir yang belum disimpan. Tidak ada
       autosave di panel ini, sehingga menutup tab atau menekan tombol kembali
       membuang pekerjaan tanpa satu pun peringatan.

    CSP: SecurityHeaders sengaja TIDAK memasang Content-Security-Policy pada
    'admin', 'admin/*' dan 'livewire/*' (Filament butuh unsafe-eval untuk
    Alpine), jadi <style> dan <script> inline di sini tidak diblokir. Bila
    suatu saat CSP diberlakukan juga untuk panel, keduanya harus dipindah ke
    berkas aset ber-nonce.
--}}

<style>
    /* Hanya di dalam formulir: halaman daftar dan dasbor tidak punya baris aksi
       seperti ini dan tidak boleh ikut terpengaruh. */
    .fi-main form .fi-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 10;
        margin-inline: -0.5rem;
        padding: 0.75rem 0.5rem;
        background-color: rgb(var(--gray-50));
        border-top: 1px solid rgb(var(--gray-200));
    }

    .dark .fi-main form .fi-form-actions {
        background-color: rgb(var(--gray-900));
        border-top-color: rgb(var(--gray-700));
    }

    /* Penanda perubahan belum disimpan, di sisi kiri baris tombol. Kosong
       selama belum ada yang diubah, sehingga tidak memakan ruang. */
    .fi-main form .fi-form-actions::before {
        content: attr(data-cms-dirty-label);
        margin-inline-end: auto;
        align-self: center;
        font-size: 0.75rem;
        color: rgb(var(--warning-600));
    }
</style>

<script>
    (function () {
        // Satu penanda per pemuatan halaman. Livewire merender ulang bagian
        // dalam formulir, tetapi elemen <form> itu sendiri bertahan, jadi
        // pendengar di bawah ini tidak perlu dipasang ulang.
        let dirty = false;

        const label = 'Perubahan belum disimpan';

        function form() {
            return document.querySelector('.fi-main form');
        }

        function mark(state) {
            dirty = state;

            const el = form();
            if (! el) {
                return;
            }

            const actions = el.querySelector('.fi-form-actions');
            if (actions) {
                actions.setAttribute('data-cms-dirty-label', state ? label : '');
            }
        }

        function attach() {
            const el = form();
            if (! el || el.dataset.cmsGuard) {
                return;
            }
            el.dataset.cmsGuard = '1';

            el.addEventListener('input', () => mark(true));
            el.addEventListener('change', () => mark(true));
            // Menyimpan lewat Livewire tetap memicu submit pada <form>.
            el.addEventListener('submit', () => mark(false));

            mark(false);
        }

        document.addEventListener('DOMContentLoaded', attach);
        document.addEventListener('livewire:navigated', attach);

        window.addEventListener('beforeunload', function (e) {
            if (! dirty) {
                return;
            }
            // Teks peringatan ditentukan browser; yang penting nilai baliknya ada.
            e.preventDefault();
            e.returnValue = '';
        });
    })();
</script>
