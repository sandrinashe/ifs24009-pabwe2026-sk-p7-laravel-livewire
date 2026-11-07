<x-auth.shell variant="member" heading="Masuk ke Akun Anda">
    <x-auth.switch active="member" page="login" />

    <div class="mb-6">
        <h1 class="font-display text-4xl font-bold text-olive-800">Masuk ke Akun Anda</h1>
        <p class="text-sm text-stone-700 mt-1">Kelola inventaris stok barang Anda secara rapi dan akurat</p>
    </div>

    <form wire:submit.prevent="authenticate" class="space-y-5">
        <x-auth.field id="login-username" label="Username atau Email" wire:model="login" error="login" autocomplete="username" placeholder="mis. member atau member@example.com" />
        <x-auth.field id="login-password" label="Password" type="password" wire:model="password" error="password" autocomplete="current-password" placeholder="Masukkan password" />

        <label class="flex items-center gap-2 text-sm text-olive-800 cursor-pointer w-fit">
            <input wire:model="remember" type="checkbox" class="rounded border-petal-300 text-petal-600 focus:ring-petal-400">
            <span>Ingat Saya</span>
        </label>

        <button type="submit" id="login-submit" wire:loading.attr="disabled"
            class="w-full py-3 px-4 bg-petal-600 hover:bg-petal-700 disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-full shadow-sm shadow-petal-600/30 transition flex items-center justify-center">
            <svg wire:loading wire:target="authenticate" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="authenticate">Masuk</span>
            <span wire:loading wire:target="authenticate">Memverifikasi...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-700">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-semibold text-petal-700 hover:text-petal-800 underline underline-offset-2">Daftar sekarang</a>
    </p>
</x-auth.shell>
