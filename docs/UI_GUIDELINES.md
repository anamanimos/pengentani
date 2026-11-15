# Panduan & Aturan Baku UI/UX Pengen Tani Console

Dokumen ini adalah pedoman wajib bagi seluruh pengembang dan agen AI dalam membangun atau memodifikasi antarmuka pengguna (UI) pada halaman Admin Console Pengen Tani.

---

## 1. Fondasi & Standar Template

1. **Design System & Template Resmi**:
   - Halaman Admin Console dibangun di atas framework **Bootstrap 5** dengan sistem komponen **Metronic 8 (Demo 1)** yang terpasang di repository (`metronic_html_v8.3.2_demo1`).
   - Segala referensi UI (baik dari Metronic maupun template pendukung seperti `http://themes.test/front-dashboard/dist/`) harus diadaptasi mengikuti sistem class dan utility resmi template yang aktif.

---

## 2. Tiga Aturan Mutlak (Strict Rules)

### 🚫 Aturan 1: HINDARI CSS Inline (`style="..."`)
- **DILARANG** menulis properti CSS langsung di tag HTML (misal: `style="margin-top: 10px; width: 100%; color: red;"`).
- **Gunakan Class Utility Template**:
  - Ukuran & Spacing: `mb-3`, `py-5`, `w-100`, `mw-150px`, `min-w-100px`.
  - Flexbox & Grid: `d-flex`, `align-items-center`, `justify-content-between`, `gap-3`.
  - Warna Teks: `text-gray-900`, `text-gray-600`, `text-primary`, `text-danger`, `text-success`.
  - Background: `bg-light`, `bg-light-primary`, `bg-body`.
  - Tipografi: `fs-6`, `fs-7`, `fw-bold`, `fw-semibold`.
- **Pengecualian**: Hanya diizinkan untuk nilai dinamis murni dari server yang tidak memiliki padanan class utility (misal: persentase progress bar `style="width: {{ $progress }}%;"`).

### 🚫 Aturan 2: HINDARI JS Inline (`onclick="..."`, `onchange="..."`)
- **DILARANG** menulis logika JavaScript di dalam atribut HTML (misal: `onclick="deleteItem(1)"`, `onchange="filterData()"`).
- **Gunakan Data Attributes & Event Listeners**:
  - Simpan data di atribut HTML5: `data-id="{{ $item->id }}"`, `data-action="delete"`, `data-url="{{ route(...) }}"`.
  - Daftarkan event listener di block `@push('scripts')`:
    ```javascript
    $(document).on('click', '[data-action="delete"]', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        // Logika eksekusi
    });
    ```

### 🚫 Aturan 3: HINDARI Membuat Class Baru di Luar Template
- **DILARANG** membuat class CSS baru ad-hoc (misal: `.kotak-penarikan`, `.tombol-simpan-baru`, `.custom-header-wrapper`).
- **Maksimalkan Class Template Bawaan**:
  - Struktur Kartu: `.card`, `.card-flush`, `.card-body`, `.card-header`.
  - Tombol: `.btn`, `.btn-sm`, `.btn-primary`, `.btn-light`, `.btn-light-danger`, `.btn-icon`.
  - Badge Status: `.badge`, `.badge-light-success`, `.badge-light-warning`, `.badge-light-danger`.
  - Tabel: `.table`, `.table-row-dashed`, `.table-sm`, `.align-middle`, `.gy-3`.
- **Pengecualian**: Hanya diizinkan untuk styling komponen pihak ketiga khusus (seperti sel lembar kerja Jspreadsheet atau light/dark mode override) yang ditempatkan secara terpusat di `resources/views/layouts/metronic.blade.php`.

---

## 3. Kompatibilitas Sidebar Minimized (Pengecilan Sidebar)

Saat sidebar berada dalam mode minimized (`data-kt-app-sidebar-minimize="on"`):

1. **Submenu Bertingkat (Accordion / Dropdown)**:
   - Setiap menu item yang memiliki anak (sub-menu) wajib memiliki:
     ```html
     <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" 
          data-kt-menu-placement="right-start" 
          class="menu-item menu-accordion {{ request()->routeIs(...) ? 'here show' : '' }}">
         <span class="menu-link">...</span>
         <div class="menu-sub menu-sub-accordion menu-sub-dropdown px-2 py-4 w-200px mh-75 overflow-auto">
             ...
         </div>
     </div>
     ```
   - Class `menu-sub-dropdown` dan `data-kt-menu-placement="right-start"` memastikan submenu muncul sebagai flyout mengambang ke kanan saat sidebar mini.
2. **Sinkronisasi State Persisten**:
   - Status toggle disimpan di `localStorage` dan Cookie (`sidebar_minimize_state`).
   - Tombol `#kt_app_sidebar_toggle` wajib mendapatkan class `.active` saat sidebar dalam keadaan mini agar rotasi ikon panah tidak terbalik.
3. **Respon Komponen Lebar Penuh (Tables / Cards)**:
   - Event `window.dispatchEvent(new Event('resize'))` harus dipicu saat toggle sidebar berubah agar Jspreadsheet, DataTables, dan chart otomatis menyesuaikan lebar kontainer tanpa merusak layout.

---

## 4. Standar Komponen UI

| Elemen | Class Standar Template |
| :--- | :--- |
| **Kartu Konten** | `card card-flush shadow-sm mb-5 mb-xl-10` |
| **Header Kartu** | `card-header pt-5` dengan `card-title` dan `card-toolbar` |
| **Tabel Data** | `table align-middle table-row-dashed fs-7 gy-3` |
| **Form Input** | `form-control form-control-solid` |
| **Form Select** | `form-select form-select-solid` |
| **Tombol Utama** | `btn btn-primary btn-sm` |
| **Tombol Batal / Sekunder** | `btn btn-light btn-sm` |
| **Tombol Hapus / Bahaya** | `btn btn-light-danger btn-sm` (atau `btn-icon`) |
| **Ikon** | KeenIcons: `<i class="ki-duotone ki-[nama-ikon] fs-2"><span class="path1"></span>...</i>` |
| **Badge Sukses** | `badge badge-light-success` |
| **Badge Peringatan** | `badge badge-light-warning` |
| **Badge Error / Kritis** | `badge badge-light-danger` |
| **Badge Informasi** | `badge badge-light-info` atau `badge-light-primary` |

---

## 5. Ringkasan Checklist Pengujian UI
Sebelum mempublikasikan perubahan UI pada halaman Admin Console:
- [ ] Sidebar dapat dikecilkan (minimize) dan dibesarkan tanpa merusak tata letak konten.
- [ ] Saat sidebar mini, menu bertingkat (seperti menu Master) dapat dibuka dengan hover/klik ke arah kanan.
- [ ] Tombol panah toggle sidebar menghadap ke arah yang benar (tersinkronisasi saat reload).
- [ ] Tidak ada atribut `style="..."` inline yang ditambahkan untuk kebutuhan layout/posisi/warna.
- [ ] Tidak ada atribut `onclick="..."` atau event JS inline.
- [ ] Tidak ada class CSS baru di luar framework template.
- [ ] Tampilan rapi baik di Light Mode maupun Dark Mode.
