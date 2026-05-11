-- Database schema for SIMagang (3NF Normalized)
-- Run this script in MySQL / MariaDB to create the database and tables.

CREATE DATABASE IF NOT EXISTS magang_tkk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE magang_tkk;

-- 1. Tabel user: Kredensial login (Hanya untuk ketua kelompok dan korbid yang bisa login ke website)
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    no_tlp VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa','korbid') NOT NULL DEFAULT 'mahasiswa',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel mahasiswa: Menyimpan biodata semua mahasiswa (baik ketua maupun anggota biasa) (3NF)
-- Memisahkan entitas mahasiswa agar nim, nama, email anggota tidak menyebabkan transitive dependency di anggota_kelompok
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL,
    no_tlp VARCHAR(30) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabel perusahaan: Memisahkan entitas perusahaan (3NF)
CREATE TABLE IF NOT EXISTS perusahaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(191) NOT NULL,
    nama_pimpinan VARCHAR(191) NOT NULL,
    bidang VARCHAR(191) NOT NULL,
    telepon VARCHAR(50) NOT NULL,
    alamat TEXT NOT NULL,
    latitude VARCHAR(30) DEFAULT NULL,
    longitude VARCHAR(30) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Tabel dosen: Memisahkan entitas dosen pembimbing (3NF)
CREATE TABLE IF NOT EXISTS dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(191) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Tabel kelompok: Entitas grup magang. ketua_user_id mengacu pada tabel user (yang login)
CREATE TABLE IF NOT EXISTS kelompok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(191) NOT NULL,
    ketua_user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ketua_user_id) REFERENCES user(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. Tabel anggota_kelompok: Relasi antara kelompok dan mahasiswa (3NF)
CREATE TABLE IF NOT EXISTS anggota_kelompok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL,
    mahasiswa_id INT NOT NULL, -- Merujuk ke entitas mahasiswa (bukan user login)
    peran ENUM('ketua','anggota') NOT NULL DEFAULT 'anggota',
    status_berkas ENUM('lengkap','belum','pending') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (kelompok_id, mahasiswa_id),
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Tabel pendaftaran_lokasi: Relasi kelompok ke perusahaan
CREATE TABLE IF NOT EXISTS pendaftaran_lokasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL UNIQUE,
    perusahaan_id INT NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    catatan TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE,
    FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 8. Tabel proposal
CREATE TABLE IF NOT EXISTS proposal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Tabel berkas_anggota
CREATE TABLE IF NOT EXISTS berkas_anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id INT NOT NULL,
    jenis_berkas ENUM('formulir','ktm','transkrip','pas_foto','cv') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota_kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Tabel bukti_diterima
CREATE TABLE IF NOT EXISTS bukti_diterima (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL UNIQUE,
    perusahaan_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE,
    FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 11. Tabel plotting: Relasi kelompok ke dosen
CREATE TABLE IF NOT EXISTS plotting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL UNIQUE,
    dosen_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
