# 🎯 INSTRUKSI PERBAIKAN FORM KYC - GACOR666

## ✅ SUDAH SELESAI DIPERBAIKI:

### 1. File `kyc_form.php` 
- ✅ Dipindahkan ke root folder
- ✅ Path config dan navbar sudah benar
- ✅ Struktur HTML lengkap dengan meta tags

### 2. File `navbar.php`
- ✅ Struktur HTML duplikat dihapus
- ✅ Hanya berisi komponen navbar

### 3. Database
- ✅ Script update database sudah dibuat (`update_database.php`)

### 4. File `.htaccess`
- ✅ Proteksi folder uploads diperbaiki
- ✅ Blokir PHP, izinkan gambar

---

## 🚀 LANGKAH YANG HARUS ANDA LAKUKAN:

### STEP 1: Update Database (PENTING!)

Buka browser dan akses:
```
http://localhost/gacor666/update_database.php
```

Anda akan melihat pesan seperti ini:
- ✅ Kolom 'kk_file' berhasil ditambahkan.
- ✅ Kolom 'surat_polisi_file' berhasil ditambahkan.
- ✅ Kolom 'foto_diri_file' berhasil ditambahkan.

### STEP 2: Test Form KYC

1. **Login sebagai user:**
   ```
   http://localhost/gacor666/login.php
   Email: a@a.a
   Password: a
   ```

2. **Akses form KYC:**
   ```
   http://localhost/gacor666/kyc_form.php
   ```

3. **Upload 4 file:**
   - Foto KTP (.jpg/.png, max 2MB)
   - Foto KK (.jpg/.png, max 2MB)
   - Surat Pengantar (.jpg/.png, max 2MB)
   - Foto Selfie dengan KTP (.jpg/.png, max 2MB)

4. **Submit Form**
   - Jika berhasil: redirect ke dashboard dengan pesan sukses
   - File akan tersimpan di folder `uploads/`
   - Status verifikasi berubah menjadi "pending"

### STEP 3: Hapus File Temporary (Setelah Update Berhasil)

```powershell
Remove-Item "c:\xampp\htdocs\Gacor666\update_database.php" -Force
```

---

## 📋 VERIFIKASI HASIL:

### Cek Database:
1. Buka phpMyAdmin
2. Pilih database `db_crowdfunding`
3. Klik tabel `users`
4. Cek kolom baru: `kk_file`, `surat_polisi_file`, `foto_diri_file` ✓

### Cek Upload:
1. Setelah submit form
2. Buka folder: `c:\xampp\htdocs\Gacor666\uploads\`
3. Harus ada 4 file baru dengan format: `ktp_[user_id]_[timestamp].jpg`

### Cek Status User:
1. Di dashboard, status verifikasi harus: "Menunggu Verifikasi" (pending)
2. Tombol "Buat Kampanye" masih disabled sampai admin approve

---

## 🐛 TROUBLESHOOTING:

### Error: "Column not found: 1054 Unknown column 'kk_file'"
**Solusi:** Anda belum menjalankan STEP 1. Akses `update_database.php` terlebih dahulu.

### Error: "failed to open stream: No such file or directory"
**Solusi:** Path salah. Pastikan file `kyc_form.php` ada di root folder (bukan di `uploads/`)

### Upload gagal: "Gagal upload ktp"
**Solusi:** 
- Pastikan folder `uploads/` ada dan writable (chmod 755)
- Cek ukuran file (max 2MB)
- Cek format file (hanya .jpg, .jpeg, .png)

---

## 📂 STRUKTUR FILE SETELAH PERBAIKAN:

```
Gacor666/
├── kyc_form.php              ← BARU (di root, bukan di uploads/)
├── update_database.php       ← BARU (hapus setelah dijalankan)
├── includes/
│   └── navbar.php            ← DIPERBAIKI (tanpa struktur HTML)
├── uploads/
│   └── .htaccess             ← DIPERBAIKI (proteksi folder)
└── (file lainnya tidak berubah)
```

---

## ✨ SELESAI!

Jika semua langkah diikuti, form KYC sekarang sudah:
- ✅ Bisa diakses
- ✅ Bisa upload 4 file
- ✅ Data tersimpan ke database
- ✅ Status user berubah ke "pending"
- ✅ File tersimpan di folder uploads/

**Silakan test dan kabari jika ada error lagi!** 🎉
