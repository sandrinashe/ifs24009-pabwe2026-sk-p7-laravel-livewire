<div>
    @php
        $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $filterChips = ['' => 'Semua'] + collect($statuses)->mapWithKeys(fn ($st) => [$st->value => $st->label()])->all();
    @endphp

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-petal-700"><span aria-hidden="true">✦</span> Inventaris</p>
            <h1 class="font-display text-4xl sm:text-5xl font-bold text-olive-800 leading-tight">Daftar <em class="text-petal-600">Stok</em> Saya</h1>
            <p class="text-sm text-stone-700 mt-1">Kelola barang, pantau jumlah, dan ketahui kapan harus restock.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="openCreateModal" wire:loading.attr="disabled" wire:target="openCreateModal"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-petal-600 hover:bg-petal-700 disabled:opacity-70 text-white text-sm font-semibold shadow-sm shadow-petal-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Tambah Stok
            </button>
            <button type="button" wire:click="openAiModal" wire:loading.attr="disabled" wire:target="openAiModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-sage-100 hover:bg-sage-200 border border-sage-300 text-sage-900 text-sm font-semibold transition disabled:opacity-70">
                <span aria-hidden="true">✦</span> Generate AI
            </button>
            <button type="button" wire:click="openImportModal" wire:loading.attr="disabled" wire:target="openImportModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white hover:bg-petal-50 border border-petal-200 text-olive-800 text-sm font-semibold transition disabled:opacity-70">
                <svg class="w-4 h-4 text-petal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import
            </button>
            <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white hover:bg-petal-50 border border-petal-200 text-olive-800 text-sm font-semibold transition disabled:opacity-70">
                <svg class="w-4 h-4 text-petal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span wire:loading.remove wire:target="exportExcel">Export</span>
                <span wire:loading wire:target="exportExcel">Mengekspor...</span>
            </button>
        </div>
    </div>

    <!-- Summary -->
    <section aria-label="Ringkasan stok" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="rounded-3xl bg-petal-100 border border-petal-200 p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-petal-800">Jenis Barang</p>
            <p class="font-display text-4xl font-bold text-petal-800 mt-1" id="summary-items">{{ $summary['items'] }}</p>
            <p class="text-xs text-petal-800/80 mt-0.5">item terdaftar</p>
        </div>
        <div class="rounded-3xl bg-sage-100 border border-sage-200 p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-sage-800">Total Unit</p>
            <p class="font-display text-4xl font-bold text-sage-800 mt-1" id="summary-units">{{ number_format($summary['units'], 0, ',', '.') }}</p>
            <p class="text-xs text-sage-800/80 mt-0.5">nilai {{ $fmtRp($summary['value']) }}</p>
        </div>
        <div class="rounded-3xl bg-amber-100 border border-amber-200 p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-900">Stok Menipis</p>
            <p class="font-display text-4xl font-bold text-amber-900 mt-1" id="summary-low">{{ $summary['low'] }}</p>
            <p class="text-xs text-amber-900/80 mt-0.5">segera restock</p>
        </div>
        <div class="rounded-3xl bg-white border border-rose-200 p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-800">Stok Habis</p>
            <p class="font-display text-4xl font-bold text-rose-700 mt-1" id="summary-out">{{ $summary['out'] }}</p>
            <p class="text-xs text-rose-800/80 mt-0.5">perlu diisi ulang</p>
        </div>
    </section>

    <!-- Filter & Search -->
    <div class="bg-white/90 p-3 sm:p-4 rounded-3xl shadow-sm border border-petal-100 mb-6 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
        <div class="w-full md:w-80 relative">
            <label for="stock-search" class="sr-only">Cari nama, SKU, atau deskripsi</label>
            <svg class="w-4 h-4 text-stone-600 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="stock-search" wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, SKU, atau deskripsi..."
                class="w-full pl-10 pr-9 py-2.5 text-sm rounded-full border border-petal-200 bg-cream focus:outline-none focus:ring-2 focus:ring-petal-400 focus:border-transparent placeholder:text-stone-600">
            <div wire:loading wire:target="search,statusFilter" class="absolute right-3.5 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-petal-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Filter status stok">
            @foreach ($filterChips as $value => $label)
                <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                    aria-pressed="{{ $statusFilter === (string) $value ? 'true' : 'false' }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition {{ $statusFilter === (string) $value ? 'bg-petal-600 border-petal-600 text-white' : 'bg-white border-petal-200 text-olive-800 hover:bg-petal-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Stock Cards -->
    @if ($stocks->isEmpty())
        <div class="rounded-3xl border border-dashed border-petal-300 bg-white/80 py-16 px-6 text-center">
            <p class="text-4xl text-petal-600" aria-hidden="true">✦</p>
            <p class="font-display text-2xl font-bold text-olive-800 mt-2">Belum ada stok barang ditemukan</p>
            <p class="text-sm text-stone-700 mt-1">Tambah stok baru atau generate otomatis menggunakan AI.</p>
        </div>
    @else
        <ul class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5" aria-label="Daftar stok barang">
            @foreach ($stocks as $stock)
                @php
                    $statusEnum = \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status);
                    $cover = $stock->coverImageUrl();
                    $level = min(100, (int) round(($stock->quantity / max($stock->min_stock * 3, 1)) * 100));
                @endphp
                <li wire:key="stock-{{ $stock->id }}"
                    class="flex flex-col bg-white rounded-3xl border border-petal-100 shadow-sm hover:shadow-lg hover:shadow-petal-200/50 hover:-translate-y-0.5 transition">
                    <div class="flex gap-4 p-4 sm:p-5">
                        <!-- Thumbnail (kecil, rasio tetap) -->
                        <a href="{{ route('stocks.show', $stock->id) }}" class="shrink-0 block w-20 h-20 rounded-2xl overflow-hidden bg-petal-50 border border-petal-100" tabindex="-1" aria-hidden="true">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="" width="80" height="80" loading="lazy" class="w-full h-full object-cover">
                            @else
                                <span class="w-full h-full flex items-center justify-center text-petal-300">
                                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </span>
                            @endif
                        </a>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('stocks.show', $stock->id) }}"
                                    class="font-display text-xl font-bold leading-tight text-olive-800 hover:text-petal-700 transition line-clamp-2">
                                    {{ $stock->name }}
                                </a>

                                <!-- Row Actions Menu -->
                                <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                                    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true"
                                        aria-label="Menu tindakan {{ $stock->name }}"
                                        class="p-1.5 -mr-1.5 rounded-full text-stone-600 hover:bg-petal-50 hover:text-petal-700 transition">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                    </button>
                                    <div x-show="open" x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute right-0 z-20 mt-1.5 w-48 origin-top-right rounded-2xl border border-petal-100 bg-white py-1.5 shadow-xl shadow-stone-200/70">
                                        <a href="{{ route('stocks.show', $stock->id) }}" @click="open = false"
                                            class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-semibold text-olive-800 hover:bg-sage-50 transition">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sage-100 text-sage-700" aria-hidden="true">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </span>
                                            Lihat Detail
                                        </a>
                                        <button type="button" @click="open = false; $wire.openEditModal({{ $stock->id }})"
                                            class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-semibold text-olive-800 hover:bg-petal-50 transition">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-petal-100 text-petal-700" aria-hidden="true">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </span>
                                            Edit Stok
                                        </button>
                                        <div class="my-1 border-t border-petal-100"></div>
                                        <button type="button" @click="open = false; $wire.confirmDelete({{ $stock->id }})"
                                            class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 transition">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700" aria-hidden="true">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </span>
                                            Hapus Stok
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $statusEnum?->badgeClass() ?? 'bg-stone-100 text-stone-700 border-stone-200' }}">
                                    {{ $statusEnum?->label() ?? $stock->status }}
                                </span>
                                @if ($stock->sku)
                                    <span class="px-2 py-0.5 text-xs font-mono rounded-full bg-cream-100 border border-petal-100 text-olive-700">{{ $stock->sku }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Level stok -->
                    <div class="px-4 sm:px-5">
                        <div class="flex items-center justify-between text-xs text-stone-700 mb-1">
                            <span>Level stok</span>
                            <span>Min. {{ $stock->min_stock }} {{ $stock->unit }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-petal-100 overflow-hidden" role="progressbar" aria-label="Level stok {{ $stock->name }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $level }}">
                            <div class="h-full rounded-full {{ $statusEnum?->barClass() ?? 'bg-stone-400' }}" style="width: {{ $level }}%"></div>
                        </div>
                    </div>

                    <div class="mt-auto flex items-end justify-between gap-3 p-4 sm:p-5 pt-4">
                        <div class="min-w-0">
                            <p class="text-xs text-stone-700">Jumlah stok</p>
                            <p class="text-3xl font-bold text-olive-800 leading-none" id="qty-{{ $stock->id }}">{{ $stock->quantity }}<span class="ml-1 text-sm font-medium text-stone-700">{{ $stock->unit }}</span></p>
                            <p class="mt-1.5 text-xs text-stone-700 truncate">
                                @if ($stock->price !== null)
                                    {{ $fmtRp($stock->price) }} / {{ $stock->unit }}
                                @else
                                    Harga belum diisi
                                @endif
                            </p>
                        </div>

                        <!-- Adjust Quantity -->
                        <div class="inline-flex items-center rounded-full border border-petal-200 bg-petal-50 p-1 shrink-0">
                            <button type="button" wire:click="adjustQuantity({{ $stock->id }}, -1)" wire:loading.attr="disabled" wire:target="adjustQuantity({{ $stock->id }}, -1)"
                                id="minus-{{ $stock->id }}" aria-label="Kurangi jumlah {{ $stock->name }}"
                                class="w-9 h-9 rounded-full bg-white text-petal-700 hover:bg-petal-600 hover:text-white text-lg font-bold leading-none shadow-sm transition disabled:opacity-60">&minus;</button>
                            <span class="w-3"></span>
                            <button type="button" wire:click="adjustQuantity({{ $stock->id }}, 1)" wire:loading.attr="disabled" wire:target="adjustQuantity({{ $stock->id }}, 1)"
                                id="plus-{{ $stock->id }}" aria-label="Tambah jumlah {{ $stock->name }}"
                                class="w-9 h-9 rounded-full bg-petal-600 text-white hover:bg-petal-700 text-lg font-bold leading-none shadow-sm transition disabled:opacity-60">+</button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $stocks->links() }}
        </div>
    @endif

    <!-- Create / Edit Form Modal -->
    @if ($showFormModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-olive-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-xl border border-petal-100 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-petal-600">
                    <h3 class="text-lg font-bold text-white">
                        {{ $editingStockId ? 'Edit Stock' : 'Tambah Stock Baru' }}
                    </h3>
                    <button type="button" @click="show = false; $wire.closeFormModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveStock" class="flex flex-col flex-1 min-h-0">
                    <div class="space-y-4 px-6 py-5 overflow-y-auto flex-1">
                        <div>
                            <label for="sl-formName" class="block text-sm font-medium text-stone-700 mb-1">Nama Barang</label>
                            <input id="sl-formName" wire:model="formName" type="text"
                                class="w-full px-3.5 py-2 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 text-sm"
                                placeholder="Contoh: Kertas HVS A4 80gsm" required>
                            @error('formName')
                                <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="block text-sm font-medium text-stone-700">Deskripsi (Toast UI Editor)</span>
                                <span class="text-xs text-stone-600">Markdown & WYSIWYG</span>
                            </div>

                            <div wire:ignore
                                 x-data="{
                                     desc: @entangle('formDescription'),
                                     editor: null,
                                     async init() {
                                         await window.loadToastUi();
                                         this.editor = new toastui.Editor({
                                             el: this.$refs.formEditorContainer,
                                             height: '260px',
                                             initialEditType: 'wysiwyg',
                                             previewStyle: 'vertical',
                                             initialValue: this.desc || '',
                                             placeholder: 'Deskripsi barang dalam format teks atau markdown...',
                                             events: {
                                                 change: () => {
                                                     this.desc = this.editor.getMarkdown();
                                                 }
                                             }
                                         });

                                         this.$watch('desc', value => {
                                             if (this.editor && value !== this.editor.getMarkdown()) {
                                                 this.editor.setMarkdown(value || '', false);
                                             }
                                         });
                                     }
                                 }"
                                 class="rounded-xl border border-stone-300 overflow-hidden bg-white">
                                <div x-ref="formEditorContainer"></div>
                            </div>
                            @error('formDescription')
                                <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="sl-formSku" class="block text-sm font-medium text-stone-700 mb-1">SKU</label>
                                <input id="sl-formSku" wire:model="formSku" type="text" placeholder="ATK-001"
                                    class="w-full px-3.5 py-2 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 text-sm bg-white">
                                @error('formSku')
                                    <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="sl-formUnit" class="block text-sm font-medium text-stone-700 mb-1">Satuan (Unit)</label>
                                <input id="sl-formUnit" wire:model="formUnit" type="text" placeholder="pcs"
                                    class="w-full px-3.5 py-2 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 text-sm bg-white">
                                @error('formUnit')
                                    <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="sl-formQuantity" class="block text-sm font-medium text-stone-700 mb-1">Jumlah (Quantity)</label>
                                <input id="sl-formQuantity" wire:model="formQuantity" type="number" min="0"
                                    class="w-full px-3.5 py-2 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 text-sm bg-white">
                                @error('formQuantity')
                                    <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="sl-formPrice" class="block text-sm font-medium text-stone-700 mb-1">Harga (Price)</label>
                                <input id="sl-formPrice" wire:model="formPrice" type="number" min="0" step="0.01"
                                    class="w-full px-3.5 py-2 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 text-sm bg-white">
                                @error('formPrice')
                                    <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="sl-formMinStock" class="block text-sm font-medium text-stone-700 mb-1">Min. Stok</label>
                                <input id="sl-formMinStock" wire:model="formMinStock" type="number" min="0"
                                    class="w-full px-3.5 py-2 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 text-sm bg-white">
                                @error('formMinStock')
                                    <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-stone-600">Status (Tersedia / Stok Menipis / Stok Habis) ditentukan otomatis dari jumlah dibanding min. stok.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-petal-50 border-t border-petal-100">
                        <button type="button" @click="show = false; $wire.closeFormModal()"
                            class="px-4 py-2 text-sm font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-petal-600 hover:bg-petal-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="saveStock"
                                class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveStock">Simpan</span>
                            <span wire:loading wire:target="saveStock">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Import Excel Modal -->
    @if ($showImportModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-olive-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-xl border border-petal-100 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-sage-600">
                    <h3 class="text-lg font-bold text-white">Import Stok dari File Excel</h3>
                    <button type="button" @click="show = false; $wire.closeImportModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="importExcel" class="flex flex-col">
                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <label for="sl-importFile" class="block text-sm font-medium text-stone-700 mb-1">Pilih File (.xlsx, .xls,
                                .csv)</label>
                            <input id="sl-importFile" wire:model="importFile" type="file" accept=".xlsx,.xls,.csv"
                                class="w-full text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100"
                                required>
                            @error('importFile')
                                <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="p-3 bg-stone-50 rounded-lg border border-petal-100 text-xs text-stone-700 space-y-1">
                            <p class="font-semibold text-stone-700">Format Kolom Spreadsheet:</p>
                            <p>Kolom A: No | B: Name | C: SKU | D: Description | E: Quantity | F: Unit | G: Price | H: Min Stock (status dihitung otomatis)</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-sage-50 border-t border-sage-100">
                        <button type="button" @click="show = false; $wire.closeImportModal()"
                            class="px-4 py-2 text-sm font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-sage-600 hover:bg-sage-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="importExcel,importFile"
                                class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="importExcel,importFile">Upload & Import</span>
                            <span wire:loading wire:target="importExcel,importFile">Mengupload & Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- AI Stock Generator Modal -->
    @if ($showAiModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-olive-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-xl border border-petal-100 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-sage-600">
                    <div class="flex items-center space-x-3">
                        <span class="p-2 bg-white/20 text-white rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-white">Generate Stok Otomatis dengan AI</h3>
                            <p class="text-xs text-sage-100">Minta AI membuatkan beberapa item stok barang terstruktur sesuai
                                kebutuhan Anda</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false; $wire.closeAiModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto flex-1 px-6 py-5">
                    @if ($aiErrorMessage)
                        <div
                            class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                            {{ $aiErrorMessage }}
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div>
                            <label for="sl-aiPrompt" class="block text-sm font-medium text-stone-700 mb-1">Permintaan Anda /
                                Prompt</label>
                            <textarea id="sl-aiPrompt" wire:model="aiPrompt" rows="3"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-stone-300 focus:outline-none focus:ring-2 focus:ring-sage-500 text-sm"
                                placeholder="Contoh: Buatkan rencana belajar Laravel Livewire selama 3 hari untuk pemula..."></textarea>
                            @error('aiPrompt')
                                <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <label for="sl-aiCount" class="text-xs font-medium text-stone-700">Jumlah item:</label>
                                    <input id="sl-aiCount" wire:model="aiCount" type="number" min="1"
                                        class="w-20 px-2.5 py-1 text-xs rounded-lg border border-stone-300 bg-white focus:outline-none focus:ring-2 focus:ring-sage-500 font-medium text-center"
                                        placeholder="3">
                                    <span class="text-xs text-stone-600">item</span>
                                </div>
                                @error('aiCount')
                                    <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button wire:click="generateWithAi" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-sage-600 hover:bg-sage-700 disabled:opacity-75 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center justify-center space-x-2">
                                <svg wire:loading wire:target="generateWithAi"
                                    class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateWithAi">Generate Sekarang</span>
                                <span wire:loading wire:target="generateWithAi">Sedang memproses AI...</span>
                            </button>
                        </div>
                    </div>

                    <!-- AI In-Progress Waiting Banner -->
                    <div wire:loading wire:target="generateWithAi"
                        class="p-6 bg-sage-50 rounded-xl border border-sage-200 text-center space-y-2">
                        <svg class="animate-spin h-7 w-7 text-sage-700 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-sage-900">Menghubungi AI untuk menyusun item stok...</p>
                        <p class="text-xs text-sage-700">Mohon tunggu beberapa detik, item stok terstruktur sedang
                            digenerate.</p>
                    </div>

                    <!-- AI Preview Results -->
                    @if (!empty($aiGeneratedItems))
                        <div class="pt-4 border-t border-petal-100">
                            <h4 class="text-sm font-bold text-olive-800 mb-2">Pilih item stok yang ingin ditambahkan:</h4>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach ($aiGeneratedItems as $index => $item)
                                    <label
                                        class="flex items-start space-x-3 p-3 rounded-lg border border-petal-100 bg-stone-50 hover:bg-sage-50/50 cursor-pointer transition">
                                        <input wire:model="aiSelectedIndices" value="{{ $index }}"
                                            type="checkbox"
                                            class="mt-1 rounded border-stone-300 text-sage-700 focus:ring-sage-500">
                                        <div class="flex-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-olive-900">{{ $item['name'] }}</span>
                                                <span class="text-stone-600">{{ $item['quantity'] ?? 0 }} {{ $item['unit'] ?? 'pcs' }}@if (!empty($item['sku'])) &middot; {{ $item['sku'] }}@endif</span>
                                            </div>
                                            @if (!empty($item['description']))
                                                <p class="text-stone-700 mt-1">{{ $item['description'] }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-sage-50 border-t border-sage-100">
                    <button type="button" @click="show = false; $wire.closeAiModal()"
                        class="px-4 py-2 text-sm font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-lg transition">
                        Batal
                    </button>
                    @if (!empty($aiGeneratedItems))
                        <button type="button" wire:click="saveAiGenerated" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-sage-600 hover:bg-sage-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="saveAiGenerated"
                                class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveAiGenerated">Simpan ke Stok Saya</span>
                            <span wire:loading wire:target="saveAiGenerated">Menyimpan...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Stock Confirmation Modal -->
    @if ($confirmingStockDeletionId)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-olive-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-petal-100 overflow-hidden">
                <div class="flex items-center space-x-3 px-6 py-4 bg-rose-600">
                    <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Stock</h3>
                        <p class="text-xs text-rose-100">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-stone-700">
                        Apakah Anda yakin ingin menghapus stock ini secara permanen dari daftar stok Anda?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDelete()" class="px-4 py-2 text-xs font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button"
                        x-data="{ deleting: false }"
                        x-bind:disabled="deleting"
                        @click="
                            if (deleting) return;
                            deleting = true;
                            $wire.deleteStock().finally(() => deleting = false);
                        "
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg x-show="deleting" x-cloak class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="deleting ? 'Menghapus...' : 'Ya, Hapus Stock'"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
