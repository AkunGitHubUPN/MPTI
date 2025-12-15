# 🔄 MIGRASI SISTEM KYC - DARI USER KE CAMPAIGN

## 📋 PERUBAHAN FUNDAMENTAL

### ❌ KONSEP LAMA (SALAH):
- User verifikasi KYC sekali
- Setelah verified → bisa buat kampanye kapan saja
- Dokumen KYC disimpan di tabel `users`
- Admin verifikasi user, bukan kampanye

### ✅ KONSEP BARU (BENAR):
- Setiap kampanye butuh dokumen KYC baru
- User langsung bisa buat kampanye
- Dokumen KYC disimpan di tabel `campaigns`  
- Admin verifikasi per kampanye (kampanye + dokumen sekaligus)

---

## 🗄️ PERUBAHAN DATABASE

### Tabel `users` - HAPUS kolom:
```sql
- ktp_file
- kk_file
- surat_polisi_file
- foto_diri_file
- verification_status
- is_verified
```

### Tabel `campaigns` - TAMBAH kolom:
```sql
+ ktp_file VARCHAR(255)
+ kk_file VARCHAR(255)
+ surat_polisi_file VARCHAR(255)
+ foto_diri_file VARCHAR(255)
+ admin_notes TEXT (untuk alasan reject)
```

---

## 🚀 LANGKAH-LANGKAH MIGRASI

### STEP 1: Backup Database
```sql
-- Backup database dulu!
mysqldump -u root db_crowdfunding > backup_before_migration.sql
```

### STEP 2: Jalankan Update Database
**Akses:**
```
http://localhost/gacor666/update_database_v2.php
```

**Atau manual via phpMyAdmin:**
```sql
-- Copy paste isi file: update_db_structure_v2.sql
```

### STEP 3: Hapus File Lama (Tidak Diperlukan)
```powershell
# File yang bisa dihapus:
Remove-Item "c:\xampp\htdocs\Gacor666\kyc_form.php" -Force
Remove-Item "c:\xampp\htdocs\Gacor666\update_database.php" -Force
Remove-Item "c:\xampp\htdocs\Gacor666\admin Gacor666\kyc_verification.php" -Force
Remove-Item "c:\xampp\htdocs\Gacor666\admin Gacor666\kyc_detail.php" -Force
```

### STEP 4: Test Website User
1. Login sebagai user (`a@a.a` / `a`)
2. Dashboard → tidak ada lagi status verifikasi user
3. Klik "Buat Kampanye Baru"
4. Isi form kampanye
5. Upload 4 dokumen KYC
6. Submit → kampanye status "pending"

### STEP 5: Test Admin Panel
1. Login sebagai admin (`admin@gacor.com` / `password123`)
2. Dashboard → lihat kampanye pending
3. Klik "Review Kampanye + KYC"
4. Lihat detail kampanye + 4 gambar KYC
5. Approve → status "active", muncul di homepage
6. Atau Reject + isi alasan → user lihat alasan di dashboard

---

## 📁 FILE YANG BERUBAH

### ✅ File Baru:
- `create_campaign.php` - Form buat kampanye + upload KYC
- `update_database_v2.php` - Script update database
- `update_db_structure_v2.sql` - SQL update manual

### 🔧 File Diubah:
- `dashboard.php` - Hapus status verifikasi user
- `admin Gacor666/index.php` - Hapus widget KYC pending
- `admin Gacor666/campaign_detail.php` - Tambah preview KYC + textarea reject
- `admin Gacor666/includes/admin_navbar.php` - Hapus menu "Verifikasi KYC"

### ❌ File Dihapus (Tidak Diperlukan):
- `kyc_form.php` - Verifikasi user (tidak diperlukan lagi)
- `admin Gacor666/kyc_verification.php` - List KYC user
- `admin Gacor666/kyc_detail.php` - Detail KYC user

---

## 🔄 FLOW BARU

### User Side:
```
1. Login
2. Klik "Buat Kampanye Baru"
3. Isi:
   - Judul kampanye
   - Deskripsi (min 100 karakter)
   - Target donasi
   - Batas waktu
   - Upload 4 dokumen: KTP, KK, Surat, Selfie+KTP
4. Submit
5. Kampanye status = "pending"
6. Tunggu review admin (1x24 jam)
```

### Admin Side:
```
1. Login admin
2. Dashboard → lihat kampanye pending
3. Klik "Review Kampanye + KYC"
4. Review:
   - Detail kampanye (judul, deskripsi, target)
   - Dokumen KYC (4 gambar)
   - Info pembuat
5. Approve → kampanye muncul di homepage
   ATAU
   Reject + isi alasan → user lihat alasan di dashboard
```

---

## 📊 PERBANDINGAN

| Aspek | Lama | Baru |
|-------|------|------|
| **Upload KYC** | Sekali per user | Setiap kampanye |
| **Lokasi Data** | Tabel `users` | Tabel `campaigns` |
| **Admin Review** | User → Kampanye | Kampanye + KYC sekaligus |
| **User Verified** | Permanent | Tidak ada konsep ini |
| **Alasan Reject** | Tidak ada | Ada (`admin_notes`) |

---

## ✅ CHECKLIST SETELAH MIGRASI

- [ ] Database sudah diupdate (kolom users dihapus, kolom campaigns ditambah)
- [ ] File `create_campaign.php` berfungsi (user bisa buat kampanye + upload KYC)
- [ ] Dashboard user tidak ada status verifikasi lagi
- [ ] Admin bisa review kampanye + lihat 4 gambar KYC
- [ ] Admin bisa approve/reject dengan alasan
- [ ] User lihat alasan reject di dashboard
- [ ] File lama (kyc_form.php, kyc_verification.php) sudah dihapus

---

## 🐛 TROUBLESHOOTING

### Error: "Column 'ktp_file' not found in users"
**Solusi:** Jalankan `update_database_v2.php` untuk hapus kolom dari users

### Error: "Column 'ktp_file' not found in campaigns"  
**Solusi:** Jalankan `update_database_v2.php` untuk tambah kolom ke campaigns

### Gambar KYC tidak muncul di admin
**Solusi:** Path gambar di `campaign_detail.php` harus `../../uploads/`

### Form create campaign error
**Solusi:** Cek kolom `ktp_file`, `kk_file`, `surat_polisi_file`, `foto_diri_file` sudah ada di campaigns

---

## 💡 KEUNTUNGAN SISTEM BARU

✅ **Lebih Aman:** Setiap kampanye di-verify terpisah  
✅ **Lebih Transparan:** Dokumen KYC spesifik per kampanye  
✅ **Lebih Mudah:** User tidak perlu verifikasi akun dulu  
✅ **Lebih Jelas:** Admin review kampanye + dokumen sekaligus  
✅ **Feedback Lebih Baik:** User tahu kenapa ditolak (admin_notes)  

---

**Migrasi selesai! Sistem KYC sekarang per kampanye, bukan per user.** 🎉
