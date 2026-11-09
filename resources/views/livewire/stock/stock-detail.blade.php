<div>
    <!-- Top Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('stocks.index') }}" class="p-2 rounded-xl text-stone-600 hover:text-olive-800 hover:bg-stone-200 transition" title="Kembali ke Daftar Stok" aria-label="Kembali ke Daftar Stok">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center space-x-2.5">
                    <h1 class="font-display text-3xl sm:text-4xl font-bold text-olive-800 truncate max-w-md sm:max-w-xl">{{ $stock->name }}</h1>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                        {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->label() ?? $stock->status }}
                    </span>
                </div>
                <p class="text-xs text-stone-600 mt-0.5">Dibuat pada {{ $stock->created_at ? $stock->created_at->format('d M Y H:i') : '-' }}</p>
            </div>
        </div>

        @can('update', $stock)
        <div class="flex flex-wrap items-center gap-2">
            <!-- Edit Stock Button (Opens Edit Modal) -->
            <button wire:click="openEditModal" wire:loading.attr="disabled" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-petal-600 hover:bg-petal-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg wire:loading.remove wire:target="openEditModal" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <svg wire:loading wire:target="openEditModal" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Edit Stock</span>
            </button>

            <!-- Adjust Quantity -->
            <div class="inline-flex items-center rounded-lg border border-petal-100 bg-white overflow-hidden">
                <button type="button" wire:click="adjustQuantity(-1)" wire:loading.attr="disabled" wire:target="adjustQuantity" id="detail-minus"
                    class="px-3 py-2 text-sm font-bold text-stone-700 hover:bg-rose-50 hover:text-rose-700 transition">&minus;</button>
                <span class="px-3 text-sm font-bold text-olive-900" id="detail-qty">{{ $stock->quantity }} {{ $stock->unit }}</span>
                <button type="button" wire:click="adjustQuantity(1)" wire:loading.attr="disabled" wire:target="adjustQuantity" id="detail-plus"
                    class="px-3 py-2 text-sm font-bold text-stone-700 hover:bg-sage-50 hover:text-sage-700 transition">+</button>
            </div>

            <!-- Delete Button -->
            <button wire:click="confirmDeleteStock" wire:loading.attr="disabled" class="inline-flex items-center space-x-1 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg transition disabled:opacity-75">
                <svg wire:loading.remove wire:target="confirmDeleteStock" class="w-4 h-4 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <svg wire:loading wire:target="confirmDeleteStock" class="animate-spin w-4 h-4 text-rose-700" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Hapus</span>
            </button>
        </div>
        @endcan
    </div>

    @php
        $statusEnum = \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status);
        $cover = $stock->coverImageUrl();
        $level = min(100, (int) round(($stock->quantity / max($stock->min_stock * 3, 1)) * 100));
        $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    @endphp

    <div class="space-y-6">
        <section class="grid lg:grid-cols-[17rem_1fr] gap-5" aria-label="Ringkasan barang">
            <!-- Cover (ukuran tetap, tidak lagi memenuhi layar) -->
            <div class="bg-white rounded-3xl border border-petal-100 shadow-sm p-4">
                <input type="file" wire:model="coverPhoto" id="coverPhotoInput" accept="image/*" class="hidden" aria-label="Pilih gambar cover">

                <div class="aspect-square w-full max-w-[16rem] mx-auto rounded-2xl overflow-hidden bg-petal-50 border border-petal-100 flex items-center justify-center">
                    @if ($cover)
                        <img src="{{ $cover }}?v={{ $stock->updated_at?->timestamp }}" alt="Foto {{ $stock->name }}" width="256" height="256" class="w-full h-full object-cover">
                    @else
                        <div class="text-center px-4">
                            <svg class="w-12 h-12 mx-auto text-petal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="mt-2 text-sm font-semibold text-olive-800">Belum ada cover</p>
                            <p class="text-xs text-stone-700 mt-0.5">PNG, JPG, atau GIF hingga 5MB</p>
                        </div>
                    @endif
                </div>

                @can('update', $stock)
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                        <button type="button" onclick="document.getElementById('coverPhotoInput').click()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-petal-600 hover:bg-petal-700 text-white text-xs font-semibold shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            {{ $cover ? 'Ganti Cover' : 'Upload Cover' }}
                        </button>
                        @if ($cover)
                            <button type="button" wire:click="confirmDeleteCover" wire:loading.attr="disabled" wire:target="confirmDeleteCover"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-white hover:bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold transition disabled:opacity-70">
                                Hapus Cover
                            </button>
                        @endif
                    </div>
                @endcan

                <div wire:loading wire:target="coverPhoto" class="mt-3 p-2.5 rounded-xl bg-petal-50 text-center text-xs text-petal-800 font-semibold animate-pulse">
                    Sedang mengunggah dan memproses gambar cover...
                </div>
                @error('coverPhoto')
                    <div role="alert" class="mt-3 p-2.5 rounded-xl bg-rose-50 text-center text-xs text-rose-700 font-medium">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Informasi stok -->
            <div class="bg-white rounded-3xl border border-petal-100 shadow-sm p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-petal-700"><span aria-hidden="true">✦</span> Jumlah stok</p>
                        <p class="font-display text-6xl font-bold text-olive-800 leading-none mt-1">{{ $stock->quantity }}<span class="ml-2 font-sans text-lg font-medium text-stone-700">{{ $stock->unit }}</span></p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full border {{ $statusEnum?->badgeClass() ?? 'bg-stone-100 text-stone-700 border-stone-200' }}">
                        {{ $statusEnum?->label() ?? $stock->status }}
                    </span>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs text-stone-700 mb-1.5">
                        <span>Level stok</span>
                        <span>Ambang menipis: {{ $stock->min_stock }} {{ $stock->unit }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-petal-100 overflow-hidden" role="progressbar" aria-label="Level stok" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $level }}">
                        <div class="h-full rounded-full {{ $statusEnum?->barClass() ?? 'bg-stone-400' }}" style="width: {{ $level }}%"></div>
                    </div>
                </div>

                <dl class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-petal-50 border border-petal-100 p-3.5">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-700">SKU</dt>
                        <dd class="mt-1 text-sm font-semibold font-mono text-olive-800 break-all">{{ $stock->sku ?? '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-sage-50 border border-sage-100 p-3.5">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-700">Harga satuan</dt>
                        <dd class="mt-1 text-sm font-semibold text-olive-800">{{ $stock->price !== null ? $fmtRp($stock->price) : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-sage-50 border border-sage-100 p-3.5">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-700">Nilai persediaan</dt>
                        <dd class="mt-1 text-sm font-semibold text-olive-800">{{ $stock->price !== null ? $fmtRp($stock->price * $stock->quantity) : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-petal-50 border border-petal-100 p-3.5">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-700">Min. stok</dt>
                        <dd class="mt-1 text-sm font-semibold text-olive-800">{{ $stock->min_stock }} {{ $stock->unit }}</dd>
                    </div>
                    <div class="rounded-2xl bg-petal-50 border border-petal-100 p-3.5">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-700">Dibuat</dt>
                        <dd class="mt-1 text-sm font-semibold text-olive-800">{{ $stock->created_at ? $stock->created_at->format('d M Y H:i') : '-' }}</dd>
                    </div>
                    <div class="rounded-2xl bg-petal-50 border border-petal-100 p-3.5">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-stone-700">Terakhir diperbarui</dt>
                        <dd class="mt-1 text-sm font-semibold text-olive-800">{{ $stock->updated_at ? $stock->updated_at->format('d M Y H:i') : '-' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- Description Viewer Section -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-petal-100">
            <div class="flex items-center justify-between pb-4 border-b border-petal-100 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-olive-900">Deskripsi Barang</h2>
                    <p class="text-xs text-stone-600 mt-0.5">Diformat dengan Toast UI Editor / Markdown</p>
                </div>
                @can('update', $stock)
                <button wire:click="openEditModal" class="inline-flex items-center space-x-1 px-3 py-1.5 text-xs font-semibold text-petal-600 hover:text-petal-700 hover:bg-petal-50 border border-petal-200 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Ubah Deskripsi</span>
                </button>
                @endcan
            </div>

            @if (!empty($stock->description))
                <!-- Render using Toast UI Viewer -->
                <div wire:ignore
                     x-data="{
                         async init() {
                             await window.loadToastUi();
                             toastui.Editor.factory({
                                 el: this.$refs.viewerContainer,
                                 viewer: true,
                                 initialValue: @js($stock->description)
                             });
                         }
                     }"
                     class="prose prose-slate max-w-none text-olive-800">
                    <div x-ref="viewerContainer"></div>
                </div>
            @else
                <div class="py-12 text-center text-stone-600">
                    <svg class="w-10 h-10 mx-auto mb-2 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm font-medium text-stone-700">Belum ada deskripsi untuk stock ini</p>
                    <p class="text-xs text-stone-600 mt-1">Tambahkan catatan, langkah kerja, atau daftar checklist dengan Toast UI Editor.</p>
                    <button wire:click="openEditModal" class="mt-4 px-3.5 py-1.5 text-xs font-semibold text-petal-600 hover:text-petal-700 bg-petal-50 hover:bg-petal-100 rounded-lg transition">
                        + Tambahkan Deskripsi Sekarang
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Stock Modal with Toast UI Editor -->
    @if ($showEditModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-olive-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-petal-100 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-petal-600">
                    <div>
                        <h3 class="text-lg font-bold text-white">Ubah Data Stock</h3>
                        <p class="text-xs text-petal-100">Edit nama, SKU, jumlah, harga, dan deskripsi barang</p>
                    </div>
                    <button type="button" @click="show = false; $wire.closeEditModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveStock" class="flex flex-col flex-1 min-h-0">
                    <div class="space-y-5 px-6 py-5 overflow-y-auto flex-1">
                        <!-- Nama Barang -->
                        <div>
                            <label for="sd-name" class="block text-sm font-semibold text-olive-800 mb-1">Nama Barang</label>
                            <input id="sd-name" wire:model="name" type="text" class="w-full px-4 py-2 text-sm font-medium rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500" placeholder="Masukkan judul stock..." required>
                            @error('name') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="sd-sku" class="block text-sm font-semibold text-olive-800 mb-1">SKU</label>
                                <input id="sd-sku" wire:model="sku" type="text"  class="w-full px-3.5 py-2 text-sm rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 bg-white">
                                @error('sku') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="sd-unit" class="block text-sm font-semibold text-olive-800 mb-1">Satuan (Unit)</label>
                                <input id="sd-unit" wire:model="unit" type="text"  class="w-full px-3.5 py-2 text-sm rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 bg-white">
                                @error('unit') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="sd-quantity" class="block text-sm font-semibold text-olive-800 mb-1">Jumlah (Quantity)</label>
                                <input id="sd-quantity" wire:model="quantity" type="number" min="0" class="w-full px-3.5 py-2 text-sm rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 bg-white">
                                @error('quantity') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="sd-price" class="block text-sm font-semibold text-olive-800 mb-1">Harga (Price)</label>
                                <input id="sd-price" wire:model="price" type="number" min="0" step="0.01" class="w-full px-3.5 py-2 text-sm rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 bg-white">
                                @error('price') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="sd-minStock" class="block text-sm font-semibold text-olive-800 mb-1">Min. Stok</label>
                                <input id="sd-minStock" wire:model="minStock" type="number" min="0" class="w-full px-3.5 py-2 text-sm rounded-xl border border-stone-300 focus:outline-none focus:ring-2 focus:ring-petal-500 bg-white">
                                @error('minStock') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <p class="text-xs text-stone-600">Status ditentukan otomatis dari jumlah dibanding min. stok.</p>

                        <!-- Deskripsi Stock with Toast UI Editor -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="block text-sm font-semibold text-olive-800">Deskripsi Stock (Toast UI Editor)</span>
                                <span class="text-xs text-stone-600">Markdown & WYSIWYG</span>
                            </div>

                            <div wire:ignore
                                 x-data="{
                                     desc: @entangle('description'),
                                     editor: null,
                                     async init() {
                                         await window.loadToastUi();
                                         this.editor = new toastui.Editor({
                                             el: this.$refs.detailEditorContainer,
                                             height: '350px',
                                             initialEditType: 'wysiwyg',
                                             previewStyle: 'vertical',
                                             initialValue: this.desc || '',
                                             placeholder: 'Tuliskan rincian barang dalam format teks atau markdown...',
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
                                <div x-ref="detailEditorContainer"></div>
                            </div>
                            @error('description') <span class="text-xs text-rose-700 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-petal-50 border-t border-petal-100">
                        <button type="button" @click="show = false; $wire.closeEditModal()" class="px-4 py-2 text-sm font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-6 py-2 text-sm font-semibold text-white bg-petal-600 hover:bg-petal-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-xl shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="saveStock" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveStock">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveStock">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus Stock -->
    @if ($confirmingStockDeletion)
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
                        Apakah Anda yakin ingin menghapus stock ini secara permanen?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDeleteStock()" class="px-4 py-2 text-xs font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteStock" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg wire:loading wire:target="deleteStock" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteStock">Ya, Hapus Stock</span>
                        <span wire:loading wire:target="deleteStock">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus Cover Photo -->
    @if ($confirmingCoverDeletion)
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
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Cover</h3>
                        <p class="text-xs text-rose-100">Gambar cover akan dihapus dari stock ini</p>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-stone-700">
                        Apakah Anda yakin ingin menghapus gambar cover dari stock ini?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDeleteCover()" class="px-4 py-2 text-xs font-semibold text-stone-700 bg-white hover:bg-stone-100 border border-petal-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteCoverPhoto" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg wire:loading wire:target="deleteCoverPhoto" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteCoverPhoto">Ya, Hapus Cover</span>
                        <span wire:loading wire:target="deleteCoverPhoto">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
