<x-auth.shell variant="member" heading="Buat Akun Member">
    <x-auth.switch active="member" page="register" />

    <div class="mb-6">
        <h1 class="font-display text-4xl font-bold text-olive-800">Buat Akun Member</h1>
        <p class="text-sm text-stone-700 mt-1">Daftar sekarang untuk mulai mengelola stok barang Anda</p>
    </div>

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

        <button type="submit" id="register-submit" wire:loading.attr="disabled"
            class="w-full py-3 px-4 bg-petal-600 hover:bg-petal-700 disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-full shadow-sm shadow-petal-600/30 transition flex items-center justify-center">
            <svg wire:loading wire:target="register" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="register">Daftar Akun</span>
            <span wire:loading wire:target="register">Mendaftarkan...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-700">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-petal-700 hover:text-petal-800 underline underline-offset-2">Masuk di sini</a>
    </p>
</x-auth.shell>
