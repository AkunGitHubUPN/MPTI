# 🔐 ADMIN PANEL - GACOR666

## 📋 Fitur Admin Panel

### ✅ Yang Sudah Dibuat:

1. **Dashboard Admin** (`index.php`)
   - Statistik total user, kampanye, donasi
   - Notifikasi kampanye pending
   - Notifikasi KYC pending
   - Overview data platform

2. **Kelola Kampanye** (`campaigns.php`)
   - Lihat semua kampanye (filter: all, pending, active, rejected, completed)
   - Approve/Reject kampanye dari user
   - Detail kampanye lengkap

3. **Verifikasi KYC** (`kyc_verification.php`)
   - Lihat semua user (filter: all, pending, approved, rejected, none)
   - Review dokumen KYC (KTP, KK, Surat, Foto Selfie)
   - Approve/Reject verifikasi

4. **Kelola Users** (`users.php`)
   - Lihat daftar semua user
   - Info lengkap: email, HP, role, status verifikasi

---

## 🚀 Cara Akses Admin Panel

### URL:
```
http://localhost/gacor666/admin%20Gacor666/
```

atau setelah dipindahkan:
```
http://localhost/admin%20Gacor666/
```

### Login Admin:
- **Email:** admin@gacor.com
- **Password:** password123

---

## 📁 Struktur Folder Admin

```
admin Gacor666/
├── index.php                  # Dashboard utama
├── campaigns.php              # List kampanye + filter
├── campaign_detail.php        # Detail + Approve/Reject kampanye
├── kyc_verification.php       # List KYC + filter
├── kyc_detail.php            # Review dokumen + Approve/Reject KYC
├── users.php                  # List semua users
└── includes/
    └── admin_navbar.php       # Navbar admin panel
```

---

## 🔧 Flow Proses Admin

### 1. Verifikasi KYC User
1. User daftar → upload dokumen KYC
2. Admin masuk ke **Verifikasi KYC**
3. Klik "Review" pada user pending
4. Lihat 4 dokumen: KTP, KK, Surat, Foto Selfie
5. Klik **Approve** atau **Reject**
6. Jika approve → user bisa buat kampanye

### 2. Approve Kampanye
1. User verified buat kampanye
2. Status kampanye = "pending"
3. Admin masuk ke **Kelola Kampanye**
4. Klik "Detail" pada kampanye pending
5. Review judul, deskripsi, target dana
6. Klik **Setujui** → status jadi "active" (muncul di homepage)
7. Atau klik **Tolak** → status jadi "rejected"

---

## 🎨 Perbedaan UI Admin vs User

| Feature | Admin Panel | User Panel |
|---------|-------------|------------|
| Warna Theme | Merah (Red) | Hijau (Green) |
| Logo | "A" merah | "G" hijau |
| Navbar | Admin Panel | Gacor666 |
| Akses | Admin only | Public |

---

## 🔒 Keamanan

### Proteksi yang Sudah Ada:
- ✅ Cek session login di setiap halaman
- ✅ Cek role admin (`$_SESSION['role'] === 'admin'`)
- ✅ Redirect otomatis jika bukan admin
- ✅ Flash message untuk feedback

### Path Gambar KYC:
```php
../../uploads/ktp_3_1765768481.jpg
```
Karena admin panel ada di subfolder, path naik 2 level.

---

## 📝 Cara Menggunakan Setelah Dipindahkan

Jika folder `admin Gacor666` dipindahkan keluar dari `Gacor666`:

### Before (Sekarang):
```
c:\xampp\htdocs\Gacor666\
├── index.php
├── config.php
└── admin Gacor666/
    └── index.php
```

### After (Nanti):
```
c:\xampp\htdocs\
├── Gacor666/
│   ├── index.php
│   └── config.php
└── admin Gacor666/
    └── index.php
```

### Yang Perlu Diubah:
1. **Path config.php** di semua file admin:
   ```php
   // Dari:
   require '../config.php';
   
   // Jadi:
   require '../Gacor666/config.php';
   ```

2. **Path uploads** di `kyc_detail.php`:
   ```php
   // Dari:
   ../../uploads/
   
   // Jadi:
   ../Gacor666/uploads/
   ```

3. **Link logout & ke website**:
   ```php
   // Navbar admin, ubah:
   <a href="../index.php">
   // Jadi:
   <a href="../Gacor666/index.php">
   ```

---

## 🎯 TODO (Opsional untuk Pengembangan)

- [ ] Fitur edit kampanye
- [ ] Fitur hapus kampanye
- [ ] Statistik grafik dashboard
- [ ] Export data to Excel/PDF
- [ ] Email notifikasi ke user saat approve/reject
- [ ] Activity log admin
- [ ] Kelola donasi

---

## 🐛 Troubleshooting

### Error: "Akses ditolak! Hanya admin..."
**Solusi:** Login dengan akun admin (`admin@gacor.com`)

### Error: "failed to open stream: No such file or directory"
**Solusi:** Cek path `require '../config.php'` sesuai lokasi folder

### Gambar KYC tidak muncul
**Solusi:** Cek path uploads `../../uploads/` atau sesuaikan dengan struktur folder

---

**Admin Panel siap digunakan!** 🎉
