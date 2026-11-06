# IFS24009 - PABWE 2026 Praktikum 7 Studi Kasus (Laravel 13 & Livewire)

Aplikasi **Manajemen Stok Barang (Inventaris)** dengan arsitektur **Feature-First**, dibangun memakai **Laravel 13**, **Livewire**, **MySQL**, **PhpSpreadsheet** (import/export Excel), dan integrasi **OpenAI** untuk generate item stok. Proyek ini adalah adaptasi dari latihan Todolist (Todo -> Stock).

## Fitur

**Member** (`/stocks`)
- Daftar stok: pencarian (nama / SKU / deskripsi), filter status, pagination.
- CRUD stok lewat modal: `name`, `sku`, deskripsi Markdown (Toast UI Editor), `quantity`, `unit`, `price`, `min_stock`.
- **Adjust quantity** (+ / -) dari daftar maupun detail. Status ikut menyesuaikan otomatis:
  - `out_of_stock` jika `quantity` = 0
  - `low_stock` jika `quantity` <= `min_stock`
  - `available` jika `quantity` > `min_stock`
- Detail stok (`/stocks/{stock}`): lihat/edit, cover image, deskripsi Markdown.
- Import / export Excel (kolom: No, Name, SKU, Description, Quantity, Unit, Price, Min Stock, Status, Created At).
- Generate item stok dengan AI -> preview -> pilih -> simpan.
- Profil (ubah data, password, foto).

**Admin** (`/admin/users`, `/admin/users/{user}/stocks`)
- Melihat daftar user beserta jumlah stok, dan stok tiap user secara **read-only**.

## Keamanan & Kualitas
- Middleware `role` (EnsureRole) dan `owns.stock` (EnsureStockOwnership), serta `StockPolicy`.
- Rate limit `1000-per-second`, `FORCE_HTTPS`.
- Form login memakai ID `login-username`, `login-password`, `login-submit`.
- Target coverage `app/` 100% dan `composer audit` bersih (dijaga di `Jenkinsfile`).

## Model data `stocks`

| Kolom | Keterangan |
|-------|-----------|
| user_id | FK ke users, cascade delete |
| name | Nama barang |
| sku | Kode SKU (nullable) |
| description | Teks/Markdown (nullable) |
| quantity | Jumlah stok (>= 0) |
| unit | Satuan, default `pcs` |
| price | Harga (decimal, nullable) |
| min_stock | Ambang stok menipis, default 5 |
| status | `available` / `low_stock` / `out_of_stock` |
| cover_image_path | Path cover (nullable) |

## Instalasi

```bash
cd ifs24009-pabwe2026-sk-p7-laravel-livewire
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# buat database MySQL: pabwe_p7_stock, lalu sesuaikan DB_* di .env
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Akun seeder: `admin` / `admin` (role admin) dan `member` / `member` (role member, dengan beberapa contoh stok).

Konfigurasi AI di `.env`: `OPENAI_ENDPOINT`, `OPENAI_KEY`, `OPENAI_MODEL`. Jangan commit `.env`.

## Pengujian

```bash
composer test          # phpunit + coverage
composer audit
```
