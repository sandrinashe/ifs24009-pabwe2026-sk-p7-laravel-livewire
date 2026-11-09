<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-cream">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StockApp Pro - kelola inventaris stok barang: jumlah, ambang stok menipis, import/export Excel, dan generate item dengan AI.">
    <title>{{ $title ?? 'Aplikasi Stok Barang' }} - PABWE 2026</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preload" href="{{ asset('fonts/dm-sans-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">

    <style>
        [x-cloak] { display: none !important; }
        body {
            background:
                radial-gradient(48rem 26rem at 100% -8%, #FDE3EE 0%, transparent 62%),
                radial-gradient(42rem 26rem at -8% 108%, #E6EFD6 0%, transparent 62%),
                #FFFDF8;
            background-attachment: fixed;
        }
        .font-display { letter-spacing: -0.01em; }
        html:has(.modal-scroll-lock),
        body:has(.modal-scroll-lock) {
            overflow: hidden !important;
            overscroll-behavior: none;
        }
        /* Garis renda tipis pemisah section */
        .lace {
            height: 10px;
            background: radial-gradient(circle at 8px 0, transparent 6px, #FACDE0 7px) 0 0 / 16px 10px repeat-x;
            opacity: .7;
        }
    </style>
    @livewireStyles
</head>
<body class="h-full font-sans text-olive-900 antialiased" x-data="{ sidebarOpen: false }">
    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-petal-700">Lewati ke konten utama</a>

    @auth
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-olive-900/50 z-40 md:hidden"
             x-cloak></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               aria-label="Navigasi utama"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-cream border-r border-petal-100 flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0 shadow-sm">

            <!-- Brand -->
            <div class="h-16 flex items-center px-5 border-b border-dashed border-petal-200 justify-between">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.users') : route('stocks.index') }}" class="flex items-center gap-2.5 group">
                    <span class="w-9 h-9 rounded-full bg-petal-600 text-white flex items-center justify-center text-lg shadow-sm shadow-petal-600/30 group-hover:bg-petal-700 transition" aria-hidden="true">✦</span>
                    <span class="font-display text-2xl font-bold text-olive-800 leading-none">StockApp <em class="text-petal-600">Pro</em></span>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-stone-600 hover:text-olive-800" aria-label="Tutup menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- User Summary -->
            <div class="p-4 mx-3 my-4 bg-petal-50 rounded-2xl border border-petal-100">
                <div class="flex items-center gap-3">
                    @if (auth()->user()->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->profile_photo_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_photo_path) }}" alt="Foto profil {{ auth()->user()->name }}" width="40" height="40" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                    @else
                        <div class="w-10 h-10 rounded-full {{ auth()->user()->isAdmin() ? 'bg-olive-600 text-white' : 'bg-petal-600 text-white' }} flex items-center justify-center font-bold text-sm shadow-sm" aria-hidden="true">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-olive-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-stone-600 font-mono truncate">&#64;{{ auth()->user()->username }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-dashed border-petal-200 flex items-center justify-between">
                    <span class="text-xs text-stone-600">Peran akun</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-olive-100 text-olive-800 border border-olive-200' : 'bg-sage-100 text-sage-800 border border-sage-200' }}">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 space-y-1 overflow-y-auto" aria-label="Menu utama">
                <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-olive-600">
                    <span aria-hidden="true">✦</span> Menu Utama
                </div>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users') }}"
                       @if(request()->routeIs('admin.*')) aria-current="page" @endif
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') ? 'bg-petal-100 text-petal-800 font-semibold' : 'text-olive-800 hover:bg-petal-50 hover:text-petal-700' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.*') ? 'text-petal-600' : 'text-olive-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>User Management</span>
                    </a>
                @endif

                <a href="{{ route('stocks.index') }}"
                   @if(request()->routeIs('stocks.*')) aria-current="page" @endif
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('stocks.*') ? 'bg-petal-100 text-petal-800 font-semibold' : 'text-olive-800 hover:bg-petal-50 hover:text-petal-700' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('stocks.*') ? 'text-petal-600' : 'text-olive-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Stok Saya</span>
                </a>

                <a href="{{ route('profile') }}"
                   @if(request()->routeIs('profile')) aria-current="page" @endif
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('profile') ? 'bg-petal-100 text-petal-800 font-semibold' : 'text-olive-800 hover:bg-petal-50 hover:text-petal-700' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('profile') ? 'text-petal-600' : 'text-olive-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-dashed border-petal-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-petal-700 hover:bg-petal-50 hover:text-petal-800 transition">
                        <svg class="w-5 h-5 text-petal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="md:pl-64 flex flex-col min-h-screen">
            <!-- Top Mobile Bar -->
            <header class="md:hidden h-16 bg-cream/95 backdrop-blur border-b border-petal-100 flex items-center justify-between px-4 sticky top-0 z-30">
                <button @click="sidebarOpen = true" class="p-2 rounded-xl text-olive-700 hover:bg-petal-50" aria-label="Buka menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-petal-600 text-white flex items-center justify-center text-sm" aria-hidden="true">✦</span>
                    <span class="font-display text-xl font-bold text-olive-800">StockApp <em class="text-petal-600">Pro</em></span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-olive-100 text-olive-800' : 'bg-sage-100 text-sage-800' }}">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </header>

            <!-- Page Content -->
            <main id="konten-utama" class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @if (session()->has('success'))
                    <div role="status" class="mb-6 p-4 rounded-2xl bg-sage-100 border border-sage-300 text-sage-900 flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5 text-sage-700 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div role="alert" class="mb-6 p-4 rounded-2xl bg-petal-100 border border-petal-300 text-petal-900 flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5 text-petal-700 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="border-t border-dashed border-petal-200 py-4 text-center text-xs text-stone-600 mt-auto">
                &copy; 2026 Delcom. All rights reserved.
            </footer>
        </div>
    @else
        <!-- Guest Layout (Login & Register) -->
        <div class="min-h-screen flex flex-col">
            <header class="border-b border-dashed border-petal-200 bg-cream/80 backdrop-blur">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <a href="{{ route('login') }}" class="flex items-center gap-2.5 group">
                        <span class="w-9 h-9 rounded-full bg-petal-600 text-white flex items-center justify-center text-lg shadow-sm shadow-petal-600/30 group-hover:bg-petal-700 transition" aria-hidden="true">✦</span>
                        <span class="font-display text-2xl font-bold text-olive-800 leading-none">StockApp <em class="text-petal-600">Pro</em></span>
                    </a>
                    <nav class="flex items-center gap-1 text-sm font-semibold" aria-label="Akses cepat">
                        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-full text-olive-800 hover:bg-petal-100 transition">Member</a>
                        <a href="{{ route('admin.login') }}" class="px-3 py-1.5 rounded-full text-olive-800 hover:bg-sage-100 transition">Admin</a>
                    </nav>
                </div>
            </header>

            <main id="konten-utama" class="flex-1 flex items-center justify-center p-4 sm:p-6">
                {{ $slot }}
            </main>

            <footer class="border-t border-dashed border-petal-200 py-4 text-center text-xs text-stone-600">
                &copy; 2026 Delcom. All rights reserved.
            </footer>
        </div>
    @endauth

    <script>
        // Toast UI Editor hanya dimuat saat dibutuhkan (modal/viewer), bukan di setiap halaman.
        window.loadToastUi = function () {
            if (window.__toastUiPromise) return window.__toastUiPromise;
            window.__toastUiPromise = new Promise(function (resolve, reject) {
                var base = 'https://uicdn.toast.com/editor/3.2.2/';
                var css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = base + 'toastui-editor.min.css';
                document.head.appendChild(css);
                var js = document.createElement('script');
                js.src = base + 'toastui-editor-all.min.js';
                js.onload = function () { resolve(window.toastui); };
                js.onerror = reject;
                document.head.appendChild(js);
            });
            return window.__toastUiPromise;
        };
    </script>

    @livewireScripts
</body>
</html>
