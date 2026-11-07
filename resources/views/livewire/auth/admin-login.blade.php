<x-auth.shell variant="admin" heading="Masuk sebagai Admin">
    <x-auth.switch active="admin" page="login" />

    <div class="mb-6">
        <h1 class="font-display text-4xl font-bold text-olive-800">Masuk sebagai Admin</h1>
        <p class="text-sm text-stone-700 mt-1">Halaman ini khusus administrator. Akun member silakan masuk lewat tab Member.</p>
    </div>

    <form wire:submit.prevent="authenticate" class="space-y-5">
        <x-auth.field id="login-username" label="Username atau Email Admin" wire:model="login" error="login" autocomplete="username" placeholder="mis. admin" />
        <x-auth.field id="login-password" label="Password" type="password" wire:model="password" error="password" autocomplete="current-password" placeholder="Masukkan password" />

        <label class="flex items-center gap-2 text-sm text-olive-800 cursor-pointer w-fit">
            <input wire:model="remember" type="checkbox" class="rounded border-sage-300 text-olive-600 focus:ring-sage-400">
            <span>Ingat Saya</span>
        </label>

        <button type="submit" id="login-submit" wire:loading.attr="disabled"
            class="w-full py-3 px-4 bg-olive-700 hover:bg-olive-800 disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-full shadow-sm transition flex items-center justify-center">
            <svg wire:loading wire:target="authenticate" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="authenticate">Masuk sebagai Admin</span>
            <span wire:loading wire:target="authenticate">Memverifikasi...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-700">
        Belum punya akun admin?
        <a href="{{ route('admin.register') }}" class="font-semibold text-olive-700 hover:text-olive-800 underline underline-offset-2">Daftar admin</a>
    </p>
</x-auth.shell>
