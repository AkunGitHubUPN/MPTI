-- Update Database Structure: Pindah KYC dari users ke campaigns
-- Jalankan SQL ini di phpMyAdmin

-- 1. Hapus kolom KYC dari tabel users (tidak diperlukan lagi)
ALTER TABLE `users` 
DROP COLUMN `ktp_file`,
DROP COLUMN `kk_file`,
DROP COLUMN `surat_polisi_file`,
DROP COLUMN `foto_diri_file`,
DROP COLUMN `verification_status`,
DROP COLUMN `is_verified`;

-- 2. Tambahkan kolom KYC ke tabel campaigns
ALTER TABLE `campaigns` 
ADD COLUMN `ktp_file` VARCHAR(255) NULL AFTER `gambar_url`,
ADD COLUMN `kk_file` VARCHAR(255) NULL AFTER `ktp_file`,
ADD COLUMN `surat_polisi_file` VARCHAR(255) NULL AFTER `kk_file`,
ADD COLUMN `foto_diri_file` VARCHAR(255) NULL AFTER `surat_polisi_file`,
ADD COLUMN `admin_notes` TEXT NULL AFTER `foto_diri_file`;

-- 3. Update status enum di campaigns (tambah 'pending' sebagai default)
-- Status 'pending' = menunggu verifikasi admin
-- Status 'active' = sudah diapprove admin
-- Status 'rejected' = ditolak admin
-- Status 'completed' = kampanye selesai

-- Note: Enum sudah ada, tidak perlu diubah
