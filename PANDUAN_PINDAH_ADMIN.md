# 📦 PANDUAN MEMINDAHKAN ADMIN PANEL KE LUAR

## 🎯 Tujuan
Memindahkan folder `admin Gacor666` keluar dari folder `Gacor666` agar terpisah.

---

## 📁 Struktur SEBELUM Dipindahkan

```
c:\xampp\htdocs\Gacor666\
├── index.php
├── config.php
├── dashboard.php
├── kyc_form.php
├── includes/
│   └── navbar.php
├── uploads/
│   └── (file KYC)
└── admin Gacor666/          ← FOLDER INI AKAN DIPINDAHKAN
    ├── index.php
    ├── campaigns.php
    ├── campaign_detail.php
    ├── kyc_verification.php
    ├── kyc_detail.php
    ├── users.php
    └── includes/
        └── admin_navbar.php
```

---

## 📁 Struktur SETELAH Dipindahkan

```
c:\xampp\htdocs\
├── Gacor666/                ← Folder utama website
│   ├── index.php
│   ├── config.php
│   ├── dashboard.php
│   ├── kyc_form.php
│   ├── includes/
│   │   └── navbar.php
│   └── uploads/
│       └── (file KYC)
│
└── admin Gacor666/          ← Folder admin TERPISAH
    ├── index.php
    ├── campaigns.php
    ├── campaign_detail.php
    ├── kyc_verification.php
    ├── kyc_detail.php
    ├── users.php
    └── includes/
        └── admin_navbar.php
```

---

## 🔧 LANGKAH-LANGKAH PEMINDAHAN

### STEP 1: Pindahkan Folder
```powershell
# Pindahkan folder admin ke luar
Move-Item "c:\xampp\htdocs\Gacor666\admin Gacor666" "c:\xampp\htdocs\admin Gacor666"
```

### STEP 2: Edit Semua File Admin
Ubah path `require '../config.php'` di SEMUA file admin berikut:
- `index.php`
- `campaigns.php`
- `campaign_detail.php`
- `kyc_verification.php`
- `kyc_detail.php`
- `users.php`

**Dari:**
```php
require '../config.php';
```

**Jadi:**
```php
require '../Gacor666/config.php';
```

### STEP 3: Edit File `kyc_detail.php`
Ubah path gambar uploads:

**Dari:**
```php
<img src="../../uploads/<?= htmlspecialchars($user['ktp_file']) ?>" ... >
```

**Jadi:**
```php
<img src="../Gacor666/uploads/<?= htmlspecialchars($user['ktp_file']) ?>" ... >
```

(Terjadi di 4 tempat: KTP, KK, Surat, Foto Selfie)

### STEP 4: Edit File `includes/admin_navbar.php`
Ubah link ke website dan logout:

**Dari:**
```php
<a href="../index.php" ...>
<a href="../logout.php" ...>
```

**Jadi:**
```php
<a href="../Gacor666/index.php" ...>
<a href="../Gacor666/logout.php" ...>
```

### STEP 5: Edit File `includes/navbar.php` (Folder Utama)
Ubah link Admin Panel:

**Dari:**
```php
<a href="admin%20Gacor666/index.php" ...>
```

**Jadi:**
```php
<a href="../admin%20Gacor666/index.php" ...>
```

---

## ✅ VERIFIKASI SETELAH PEMINDAHAN

### Test 1: Akses Website Utama
```
http://localhost/Gacor666/
```
✓ Harus bisa diakses normal

### Test 2: Akses Admin Panel
```
http://localhost/admin%20Gacor666/
```
✓ Harus redirect ke login jika belum login
✓ Setelah login admin, dashboard admin muncul

### Test 3: Test Fitur Admin
1. Login sebagai admin (admin@gacor.com / password123)
2. Buka Dashboard → harus tampil statistik
3. Buka Kampanye → harus tampil list kampanye
4. Buka Detail kampanye → harus bisa approve/reject
5. Buka KYC Verification → harus tampil list user
6. Buka Detail KYC → **gambar harus muncul** (ini yang penting!)
7. Approve/Reject → harus berhasil

### Test 4: Link Antar Halaman
- ✓ Dari admin panel, klik "← Ke Website" → ke homepage
- ✓ Dari admin panel, klik "Logout" → logout dan ke homepage
- ✓ Dari website, login admin, klik "Admin Panel" → ke dashboard admin

---

## 🐛 TROUBLESHOOTING

### Error: "failed to open stream: config.php"
**Penyebab:** Path `require '../config.php'` masih salah  
**Solusi:** Ubah jadi `require '../Gacor666/config.php'`

### Gambar KYC tidak muncul
**Penyebab:** Path gambar masih `../../uploads/`  
**Solusi:** Ubah jadi `../Gacor666/uploads/`

### Link "Ke Website" error 404
**Penyebab:** Link masih `../index.php`  
**Solusi:** Ubah jadi `../Gacor666/index.php`

### Admin Panel tidak bisa diakses
**Penyebab:** Folder name ada spasi, URL encode jadi `%20`  
**Solusi:** Akses dengan `http://localhost/admin%20Gacor666/`

---

## 💡 TIPS

1. **Gunakan Find & Replace:**
   - Buka semua file admin di VS Code
   - Find: `require '../config.php'`
   - Replace: `require '../Gacor666/config.php'`
   - Replace All

2. **Cek Path Setelah Edit:**
   - Buka Command Palette (Ctrl+Shift+P)
   - Cari "View: Toggle Problems"
   - Lihat jika ada error PHP

3. **Rename Folder (Opsional):**
   Jika tidak suka nama dengan spasi:
   ```powershell
   Rename-Item "c:\xampp\htdocs\admin Gacor666" "c:\xampp\htdocs\adminGacor666"
   ```
   Lalu akses: `http://localhost/adminGacor666/`

---

## 📝 CHECKLIST SEBELUM GO LIVE

- [ ] Semua path `require` sudah benar
- [ ] Semua path gambar uploads sudah benar
- [ ] Link navbar sudah benar
- [ ] Test login admin berhasil
- [ ] Test approve kampanye berhasil
- [ ] Test approve KYC berhasil
- [ ] Gambar KYC tampil dengan benar
- [ ] Flash message muncul
- [ ] Logout berfungsi

---

**Selamat! Admin Panel siap dipindahkan!** 🚀
