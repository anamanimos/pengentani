# Hacktani / PengenTani / TaniSync

Platform manajemen investasi pertanian Indonesia. Mengelola siklus penuh: investasi, pengeluaran (pembelian, upah pekerja), pemasukan (penjualan panen), distribusi bagi hasil dengan zakat, dan penarikan dana.

## Tech Stack

- **Backend**: Laravel 13.8, PHP 8.3+
- **Database**: SQLite (default) / MySQL 8.0
- **Auth**: Laravel Breeze (session-based)
- **Admin UI**: Metronic v8.3.2 (Bootstrap 5) + JSpreadsheet + ApexCharts
- **Investor UI**: Custom mobile-first (glassmorphism, green theme, Outfit font)
- **Export**: PhpSpreadsheet (XLSX)
- **Integration**: WhatsApp Gateway (`wag.nams.my.id`)

## Project Structure

```
app/
  Http/Controllers/     26 controllers
  Models/               17 Eloquent models
  Services/             WaGatewayService, LogService
  View/Components/      AppLayout, GuestLayout
database/
  migrations/           37 migration files (2026-05 to 2026-07)
  database.sqlite
resources/views/        90+ Blade templates
  investor/             investor-facing pages
  layouts/              metronic.blade.php, investor.blade.php
  pertanians/           admin CRUD
  purchases/            spreadsheet-style CRUD
  incomes/              spreadsheet-style CRUD
  worker_jobs/          spreadsheet-style CRUD
public/
  assets/               Metronic bundled assets
  css/investor-app.css  investor mobile-first CSS
routes/
  web.php               main routes
  api.php               minimal (GET /user, GET /logs)
  auth.php              Breeze auth routes
```

## Architecture

### Roles

| Role | Deskripsi |
|------|-----------|
| `admin` | Full access. Kelola semua pertanian, kebun, investor, pengelola |
| `pengelola` | Manager. Kelola operasional pertanian yang ditugaskan |
| `investor` | Investasi dana, terima bagi hasil, lihat portofolio |
| `pekerja` | Catat jam kerja dan upah |

### Routing

- `/` — Investor home dashboard
- `/portofolio`, `/peluang`, `/penarikan`, `/profile`, `/project/{uuid}` — Investor routes
- `/console/*` — Admin/management console (CRUD semua modul)
- `/autologin/{user}` — Signed URL auto-login
- `/whatsapp/webhook` — WhatsApp inbound messages
- `/login/whatsapp/{user}` — Signed WhatsApp login link

### Central Model: Pertanian

`Pertanian` adalah model pusat. Relationships:
- `Kebun` (land), `User` (admin/owner), `Entity` (pengelola)
- `PertanianTanaman` (crops), `PertanianBiaya` (estimated costs)
- `PertanianInvestor`, `PertanianUpdate`, `PertanianUpdate`
- `Purchase`, `Income`, `WorkerJob`, `Withdrawal`

Profit split per project: `persentase_admin`, `persentase_pengelola`, `persentase_investor`. Zakat: `persentase_zakat` (default 5%).

### Entity System

`Entity` — representasi investor/pengelola/perusahaan. Bisa punya banyak user via `entity_user` pivot. Memungkinkan satu entity (misal perusahaan) diwakili beberapa user.

## Database Schema

### Core Tables

- `users` — id, name, email, password, is_active, whatsapp, role
- `kebuns` — id, user_id, name, polygon (GeoJSON), area (m²), status, soft deletes
- `tanamans` — id, name, description, soft deletes
- `pertanians` — id, uuid, user_id, kebun_id, admin_id, pengelola_entity_id, name, dates, status, profit percentages, zakat %, investment cap, soft deletes
- `pertanian_tanamans` — pivot: pertanian_id, tanaman_id, qty_pohon, estimasi
- `pertanian_biayas` — pertanian_id, name, qty, harga_satuan, total
- `entities` — id, name, type (investor/pengelola/perusahaan), address, phone
- `entity_user` — entity_id, user_id, role

### Financial Tables

- `purchases` — pertanian_id, store_id, invoice_number, date, total_amount
- `purchase_items` — purchase_id, purchase_category_id, category, description, qty, unit_price, total_price, transaction_proof_id
- `purchase_categories` — id, name, description
- `incomes` — pertanian_id, date, income_category_id, description, qty, unit_price, amount, tengkulak_id, transaction_proof_id
- `income_categories` — id, name
- `worker_jobs` — pertanian_id, worker_id, job_category_id, date, start_time, end_time, wage, konsumsi, status, description, transaction_proof_id
- `job_categories` — id, name, description, soft deletes
- `stores` — id, name, address, phone (vendors/suppliers)
- `tengkulaks` — id, name, phone (crop buyers/middlemen)

### Investment & Distribution

- `pertanian_investors` — pertanian_id, entity_id, besaran_investasi, porsi_bagi_hasil, status (Deal/Standby), keterangan
- `withdrawals` — pertanian_id, type (bagi_hasil/pengembalian_modal/zakat), user_id, role, amount, proof_image, notes, date

### System Tables

- `transaction_proofs` — user_id, name, file_path, rename_history (JSON)
- `pertanian_updates` — pertanian_id, user_id, title, description, photo (JSON), date
- `activity_logs` — user_id, type, action, description, ip_address, user_agent, payload (JSON)
- `settings` — key-value store

## Key Features

### Pertanian Management
Create/edit/delete farming projects. Link kebun + tanaman. Estimate costs. Status workflow: `draft → Pencarian Investor → Sedang Berjalan → Selesai`.

### Financial Recording
- **Pembelian** (Purchases): Spreadsheet CRUD dengan JExcel. Kategori, toko, vendor.
- **Pendapatan** (Incomes): Penjualan panen. Qty/harga, tengkulak tracking.
- **Pekerjaan** (Worker Jobs): Jam kerja, upah, tunjangan makan.
- Semua: export XLSX, AJAX bulk save, buat kategori/toko on-the-fly.

### Distribusi Penarikan
Tipe: bagi hasil, pengembalian modal, zakat. Per-role tracking. Proof image upload. Running balance.

### Bukti Transaksi
Upload gambar/PDF sebagai bukti. Link ke purchases/incomes/worker_jobs. Rename history. Filter used/unused.

### WhatsApp Integration
- Login passwordless: user kirim "login" ke WA bot → terima signed URL
- Webhook terima pesan masuk
- Auto-capture gambar dari grup WA sebagai bukti transaksi

### Dashboard
- **Console**: KPI cards (total income, expense, profit, investment), trend chart 6 bulan (ApexCharts)
- **Investor**: Per-role data, investment totals, return calculations

## Authentication

4 metode login:
1. **Email/Password** — Breeze standard `/login`
2. **WhatsApp Login** — Kirim "login" ke WA → signed URL (5 menit expiry)
3. **Auto-Login Link** — `/autologin/{user}` (signed URL)
4. **Impersonation** — Admin/Pengelola bisa login sebagai user lain

Authorization: simple `$pertanian->user_id !== Auth::id()` guards. Role checks via `$user->isAdmin()`, `$user->isInvestor()`, dll. No policy classes.

## AJAX Endpoints

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/console/tanamans/ajax` | Create tanaman via AJAX |
| GET | `/console/purchases/ajax-dropdowns` | Dropdown data purchases |
| POST | `/console/purchases/ajax-store` | Create store on-the-fly |
| POST | `/console/purchases/ajax-category` | Create kategori on-the-fly |
| GET | `/console/worker-jobs/ajax-dropdowns` | Dropdown data worker jobs |
| POST | `/console/worker-jobs/ajax-worker` | Create pekerja on-the-fly |
| POST | `/console/worker-jobs/ajax-category` | Create kategori on-the-fly |
| POST | `/console/incomes/ajax-category` | Create income category on-the-fly |
| POST | `/whatsapp/webhook` | WhatsApp gateway webhook |

## Export

- `GET /console/purchases/export` — XLSX
- `GET /console/incomes/export` — XLSX
- `GET /console/worker-jobs/export` — XLSX

## Services

- `WaGatewayService` — HTTP ke WA Gateway API (`wag.nams.my.id`). Static `sendMessage()`. Basic Auth.
- `LogService` — Static `record()` untuk activity logging.

## Config

- `.env` — `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `WA_GATEWAY_USERNAME/PASSWORD`
- `config/database.php` — supports sqlite, mysql, mariadb, pgsql, sqlsrv

## Security Notes

1. `GET /logs` expose Laravel log — **no auth**
2. WhatsApp auto-login force-login meski user lain authenticated
3. Worker auto-creation pakai hardcoded password `password123`
4. No rate limiting beyond Breeze defaults
5. Impersonation hanya cek `isAdmin()` / `isPengelola()` — no granular permissions

## Key File Paths

| File | Fungsi |
|------|--------|
| `routes/web.php` | Entry point routes |
| `app/Models/Pertanian.php` | Central model |
| `app/Models/User.php` | User model + role checks |
| `app/Models/Entity.php` | Entity system |
| `database_dump.sql` | Full DB schema |
| `resources/views/layouts/metronic.blade.php` | Admin layout |
| `resources/views/layouts/investor.blade.php` | Investor layout |
| `app/Services/WaGatewayService.php` | WhatsApp integration |
| `app/Http/Controllers/InvestorDashboardController.php` | Investor dashboard |
| `app/Http/Controllers/PertanianController.php` | Pertanian CRUD + financial detail |
| `public/css/investor-app.css` | Investor mobile CSS |
