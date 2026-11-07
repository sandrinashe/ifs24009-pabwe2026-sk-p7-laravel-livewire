<x-auth.shell variant="admin" heading="Daftar Akun Admin">
    <x-auth.switch active="admin" page="register" />

    <div class="mb-6">
        <h1 class="font-display text-4xl font-bold text-olive-800">Daftar Akun Admin</h1>
        <p class="text-sm text-stone-700 mt-1">Pendaftaran admin memerlukan kode akses rahasia dari pengelola sistem.</p>
    </div>

    @unless ($enabled)
        <div role="alert" class="mb-5 p-4 rounded-2xl bg-amber-100 border border-amber-300 text-amber-900 text-sm">
            Pendaftaran admin sedang dinonaktifkan. Isi <code class="font-mono font-semibold">ADMIN_REGISTRATION_CODE</code> di file <code class="font-mono font-semibold">.env</code> untuk mengaktifkannya.
        </div>
    @endunless

    <form wire:submit.prevent="register" class="space-y-4">
        <x-auth.field id="name" label="Nama Lengkap" wire:model="name" error="name" autocomplete="name" />
        <div class="grid sm:grid-cols-2 gap-4">
            <x-auth.field id="username" label="Username" wire:model="username" error="username" autocomplete="username" />
            <x-auth.field id="email" label="Email" type="email" wire:model="email" error="email" autocomplete="email" />
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <x-auth.field id="password" label="Password" type="password" wire:model="password" error="password" autocomplete="new-password" hint="Minimal 8 karakter" />
            <x-auth.field id="password_confirmation" label="Konfirmasi Password" type="password" wire:model="password_confirmation" autocomplete="new-password" />
        </div>
        <x-auth.field id="access_code" label="Kode Akses Admin" type="password" wire:model="access_code" error="access_code" autocomplete="off" />

        <button type="submit" id="register-submit" wire:loading.attr="disabled"
            class="w-full py-3 px-4 bg-olive-700 hover:bg-olive-800 disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-full shadow-sm transition flex items-center justify-center">
            <svg wire:loading wire:target="register" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="register">Daftar sebagai Admin</span>
            <span wire:loading wire:target="register">Mendaftarkan...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-700">
        Sudah punya akun admin?
        <a href="{{ route('admin.login') }}" class="font-semibold text-olive-700 hover:text-olive-800 underline underline-offset-2">Masuk di sini</a>
    </p>
</x-auth.shell>
