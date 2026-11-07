@props(['variant' => 'member', 'heading', 'subtitle' => null])

@php
    $isAdmin = $variant === 'admin';
    $points = $isAdmin
        ? ['Lihat semua pengguna beserta ringkasan stoknya', 'Akses read-only ke stok tiap pengguna', 'Ekspor stok pengguna ke Excel']
        : ['Pantau jumlah stok dan ambang menipis', 'Import dan export data lewat Excel', 'Generate item stok dengan AI'];
@endphp

<div class="w-full max-w-5xl grid md:grid-cols-5 rounded-[2rem] overflow-hidden border border-petal-100 bg-white shadow-xl shadow-petal-200/40 my-4">
    <!-- Panel dekoratif -->
    <aside class="hidden md:flex md:col-span-2 flex-col justify-between p-9 relative overflow-hidden {{ $isAdmin ? 'bg-sage-100' : 'bg-petal-100' }}" aria-hidden="true">
        <span class="absolute -right-10 -top-10 w-44 h-44 rounded-full {{ $isAdmin ? 'bg-petal-100' : 'bg-sage-100' }} opacity-80"></span>
        <span class="absolute -left-12 -bottom-12 w-52 h-52 rounded-full {{ $isAdmin ? 'bg-sage-200' : 'bg-petal-200' }} opacity-70"></span>

        <div class="relative">
            <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/80 text-xs font-semibold uppercase tracking-wider {{ $isAdmin ? 'text-olive-800' : 'text-petal-800' }}">
                <span>✦</span> {{ $isAdmin ? 'Ruang admin' : 'Ruang member' }}
            </span>
            <p class="font-display text-5xl font-bold leading-[1.05] mt-6 {{ $isAdmin ? 'text-olive-800' : 'text-petal-800' }}">
                @if ($isAdmin)
                    Pantau semua <em class="text-petal-600">stok</em> dalam satu tempat.
                @else
                    Stok rapi, <em class="text-petal-600">restock</em> tepat waktu.
                @endif
            </p>
        </div>

        <ul class="relative space-y-3 text-sm font-medium {{ $isAdmin ? 'text-olive-800' : 'text-petal-900' }}">
            @foreach ($points as $point)
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-white/80 flex items-center justify-center text-[10px] {{ $isAdmin ? 'text-olive-700' : 'text-petal-700' }}">✦</span>
                    <span>{{ $point }}</span>
                </li>
            @endforeach
        </ul>
    </aside>

    <!-- Form -->
    <div class="md:col-span-3 p-6 sm:p-10">
        {{ $slot }}
    </div>
</div>
