# Struktur Pemisahan Backend / Frontend / Database

Folder ini sekarang memisahkan komponen utama aplikasi:

- `backend/`
  - Menyimpan logika server dan koneksi database.
  - `backend/database.php` membuka koneksi MySQL.
  - `backend/auth/` berisi handler login dan register.

- `frontend/`
  - Menyimpan halaman tampilan yang menggunakan backend sebagai endpoint.
  - `frontend/auth/` berisi halaman login dan register baru.

- `database/`
  - Menyimpan skema SQL untuk membuat database dan tabel.
  - `database/schema.sql` dapat diimpor ke MySQL atau MariaDB.

Catatan:
- Halaman lama di `auth/`, `mahasiswa/`, dan `koordinator/` tetap ada sebagai implementasi lama.
- `index.php` sekarang mengarahkan ke `frontend/auth/login.php` dan `frontend/auth/register.php`.
