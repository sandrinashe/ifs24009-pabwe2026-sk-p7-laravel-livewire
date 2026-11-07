@props(['active' => 'member', 'page' => 'login'])

@php
    $memberRoute = $page === 'login' ? route('login') : route('register');
    $adminRoute = $page === 'login' ? route('admin.login') : route('admin.register');
    $base = 'text-center px-4 py-2 rounded-full text-sm font-semibold transition';
@endphp

<nav class="grid grid-cols-2 gap-1 p-1 rounded-full bg-petal-50 border border-petal-100 mb-7" aria-label="Pilih jenis akun">
    <a href="{{ $memberRoute }}" @if ($active === 'member') aria-current="page" @endif
        class="{{ $base }} {{ $active === 'member' ? 'bg-petal-600 text-white shadow-sm' : 'text-olive-800 hover:bg-petal-100' }}">Member</a>
    <a href="{{ $adminRoute }}" @if ($active === 'admin') aria-current="page" @endif
        class="{{ $base }} {{ $active === 'admin' ? 'bg-olive-700 text-white shadow-sm' : 'text-olive-800 hover:bg-sage-100' }}">Admin</a>
</nav>
