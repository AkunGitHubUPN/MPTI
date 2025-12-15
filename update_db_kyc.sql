-- Tambahkan kolom untuk file KYC di tabel users
ALTER TABLE `users` 
ADD COLUMN `kk_file` VARCHAR(255) NULL AFTER `ktp_file`,
ADD COLUMN `surat_polisi_file` VARCHAR(255) NULL AFTER `kk_file`,
ADD COLUMN `foto_diri_file` VARCHAR(255) NULL AFTER `surat_polisi_file`;
