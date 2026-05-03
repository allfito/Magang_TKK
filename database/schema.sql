-- Database schema for SIMagang
-- Run this script in MySQL / MariaDB to create the database and tables.

CREATE DATABASE IF NOT EXISTS simagang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simagang;

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    no_tlp VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa','korbid') NOT NULL DEFAULT 'mahasiswa',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kelompok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(191) NOT NULL,
    ketua_id INT NOT NULL,
    status ENUM('aktif','menunggu','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ketua_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS anggota_kelompok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL,
    mahasiswa_id INT DEFAULT NULL,
    nama VARCHAR(191) NOT NULL DEFAULT '',
    nim VARCHAR(50) NOT NULL DEFAULT '',
    email VARCHAR(191) NOT NULL DEFAULT '',
    no_tlp VARCHAR(30) NOT NULL DEFAULT '',
    peran ENUM('ketua','anggota') NOT NULL DEFAULT 'anggota',
    status_berkas ENUM('lengkap','belum','pending') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES user(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pendaftaran_lokasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL,
    perusahaan VARCHAR(191) NOT NULL,
    nama_pimpinan VARCHAR(191) NOT NULL,
    bidang VARCHAR(191) NOT NULL,
    telepon VARCHAR(50) NOT NULL,
    alamat TEXT NOT NULL,
    latitude VARCHAR(30) DEFAULT NULL,
    longitude VARCHAR(30) DEFAULT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS proposal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS berkas_anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id INT NOT NULL,
    jenis_berkas ENUM('formulir','ktm','transkrip','pas_foto','cv') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota_kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bukti_diterima (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL,
    tempat_diterima VARCHAR(191) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plotting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelompok_id INT NOT NULL UNIQUE,
    lokasi VARCHAR(255) NOT NULL,
    dosen_pembimbing VARCHAR(191) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE CASCADE
) ENGINE=InnoDB;
