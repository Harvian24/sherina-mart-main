# Sherina Mart - Sistem Point of Sale (POS)

Aplikasi Point of Sale (POS) berbasis Laravel Zero untuk manajemen toko retail. Aplikasi ini menyediakan fitur lengkap untuk mengelola transaksi penjualan, inventori barang, dan laporan penjualan.

## 🚀 Fitur Utama

- **Transaksi Pembelian** - Sistem keranjang belanja untuk transaksi multiple items
- **Manajemen Kategori** - Tambah, ubah, hapus kategori barang
- **Manajemen Jenis Barang** - Kelola berbagai jenis/variasi barang
- **Manajemen Produk** - CRUD lengkap untuk produk dengan validasi kode unik
- **Cetak Struk** - Generate dan simpan struk transaksi otomatis
- **Laporan Penjualan** - Lihat riwayat transaksi dan cetak ulang struk
- **Database SQLite** - Penyimpanan data lokal yang ringan

## 📋 Persyaratan Sistem

- PHP >= 8.1
- Composer
- SQLite3
- Windows/Linux/MacOS

## 🔧 Instalasi

1. Clone repository ini:
```bash
git clone https://github.com/[USERNAME]/sherina-mart.git
cd sherina-mart
```

2. Install dependencies:
```bash
composer install
```

3. Jalankan migrasi database:
```bash
php application migrate
```

4. Jalankan aplikasi:
```bash
php application app:menu-command
```

## 📖 Cara Penggunaan

Setelah menjalankan aplikasi, Anda akan melihat menu utama dengan pilihan:

1. **Transaksi Pembelian Barang** - Lakukan transaksi penjualan
2. **Daftar Kategori Barang** - Lihat semua kategori
3. **Tambah Kategori Barang** - Tambah kategori baru
4. **Ubah Kategori Barang** - Edit kategori yang ada
5. **Hapus Kategori Barang** - Hapus kategori
6. **Daftar Jenis Barang** - Lihat semua jenis barang
7. **Tambah Jenis Barang** - Tambah jenis baru
8. **Ubah Jenis Barang** - Edit jenis yang ada
9. **Hapus Jenis Barang** - Hapus jenis
10. **Daftar Barang** - Lihat semua produk
11. **Tambah Barang** - Tambah produk baru
12. **Ubah Barang** - Edit produk yang ada
13. **Hapus Barang** - Hapus produk
14. **Daftar Penjualan Barang** - Lihat laporan penjualan
15. **Cetak Ulang Struk Transaksi** - Cetak ulang struk lama

## 📁 Struktur Proyek

```
sherina-mart/
├── app/
│   ├── Commands/         # Command classes
│   └── Models/          # Database models
├── database/
│   ├── migrations/      # Database migrations
│   └── database.sqlite  # SQLite database file
├── receipts/            # Folder penyimpanan struk
├── vendor/              # Composer dependencies
└── application          # Entry point aplikasi
```

## 🗄️ Database

Aplikasi menggunakan SQLite dengan struktur tabel:

- **categories** - Kategori barang
- **varieties** - Jenis/variasi barang
- **products** - Data produk
- **sale_transactions** - Transaksi penjualan

## 📝 Struk Transaksi

Setiap transaksi akan menghasilkan struk yang disimpan di folder `receipts/` dengan format:
```
YYYY-MM-DD_TRX-[ID].txt
```

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan buat pull request atau laporkan issue jika menemukan bug.

## 📄 Lisensi

Proyek ini dibuat untuk keperluan edukasi dan pembelajaran.

## 👨‍💻 Developer

Dikembangkan dengan ❤️ menggunakan Laravel Zero

---

**Sherina Mart** - Solusi POS Sederhana untuk Toko Anda
