<div id="modal-password" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-password-title">
    {{-- Latar gelap (klik untuk menutup) --}}
    <div class="fixed inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="pointer-events-none relative flex min-h-full items-center justify-center p-4">
        <div class="pointer-events-auto relative max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white shadow-xl">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 id="modal-password-title" class="text-lg font-semibold text-gray-800">Ubah Password</h2>
                <button type="button" data-modal-close class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form id="form-password" action="{{ route('ubah-password.update') }}" method="POST" class="px-6 py-5" novalidate>
                @csrf

                <div id="password-alert" class="mb-4 hidden rounded-lg border px-4 py-3 text-sm"></div>

                <div class="space-y-4">
                    @foreach ([
                        ['password_lama', 'Password Lama', 'current-password'],
                        ['password_baru', 'Password Baru', 'new-password'],
                        ['password_baru_confirmation', 'Konfirmasi Password Baru', 'new-password'],
                    ] as [$name, $label, $autocomplete])
                        <div>
                            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                            <div class="relative mt-1">
                                <input type="password" id="{{ $name }}" name="{{ $name }}" autocomplete="{{ $autocomplete }}"
                                       class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-20 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <button type="button" data-toggle-password="{{ $name }}"
                                        class="absolute inset-y-0 right-0 px-3 text-xs font-medium text-gray-500 hover:text-blue-700">
                                    Tampilkan
                                </button>
                            </div>
                            <p data-error-for="{{ $name }}" class="mt-1 text-xs text-red-600"></p>
                        </div>
                    @endforeach
                </div>

                <p class="mt-3 text-xs text-gray-500">Gunakan minimal 8 karakter.</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-modal-close
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" id="btn-simpan-password"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modal    = document.getElementById('modal-password');
    const form     = document.getElementById('form-password');
    const alertBox = document.getElementById('password-alert');
    const btnSave  = document.getElementById('btn-simpan-password');
    let closeTimer = null;

    function clearErrors() {
        form.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; });
        alertBox.classList.add('hidden');
    }

    function showAlert(type, message) {
        alertBox.className = 'mb-4 rounded-lg border px-4 py-3 text-sm ' + (type === 'success'
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800');
        alertBox.textContent = message;
    }

    function setLoading(on) {
        btnSave.disabled = on;
        btnSave.textContent = on ? 'Menyimpan...' : 'Simpan';
    }

    function openModal() {
        clearTimeout(closeTimer);
        form.reset();
        clearErrors();
        form.querySelectorAll('input[name^="password_"]').forEach(function (i) { i.type = 'password'; });
        form.querySelectorAll('[data-toggle-password]').forEach(function (b) { b.textContent = 'Tampilkan'; });
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        clearTimeout(closeTimer);
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Buka (link dengan atribut data-modal-open="modal-password"), tutup, dan tampilkan/sembunyikan password
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-modal-open="modal-password"]')) {
            e.preventDefault();
            openModal();
            return;
        }
        if (e.target.closest('#modal-password [data-modal-close]')) {
            closeModal();
            return;
        }
        const toggle = e.target.closest('#modal-password [data-toggle-password]');
        if (toggle) {
            const input  = document.getElementById(toggle.dataset.togglePassword);
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            toggle.textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const data = await res.json().catch(function () { return {}; });

            if (res.ok) {
                form.reset();
                showAlert('success', data.message || 'Password berhasil diubah.');
                closeTimer = setTimeout(closeModal, 1500);
            } else if (res.status === 422) {
                const errors = data.errors || {};
                Object.keys(errors).forEach(function (field) {
                    const el = form.querySelector('[data-error-for="' + field + '"]');
                    if (el) el.textContent = errors[field][0];
                });
            } else if (res.status === 419 || res.status === 401) {
                showAlert('error', 'Sesi Anda telah berakhir. Silakan muat ulang halaman.');
            } else if (res.status === 429) {
                showAlert('error', 'Terlalu banyak percobaan. Coba lagi beberapa saat.');
            } else {
                showAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');
            }
        } catch (err) {
            showAlert('error', 'Gagal terhubung ke server.');
        } finally {
            setLoading(false);
        }
    });
})();
</script>