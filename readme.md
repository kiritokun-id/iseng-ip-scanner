# 🔍 Iseng IP Scanner

Aplikasi web sederhana berbasis PHP dan MySQL untuk melakukan pemindaian IP (*IP Scanning*), pengelolaan subnet, dan manajemen informasi jaringan.

---

## 📸 Preview
![Demo Aplikasi](demo.png)

---

## ⚙️ Requirements / Kebutuhan Sistem

Pastikan komputer atau server Anda sudah terinstal:
* **Web Server** (Apache / Nginx, seperti yang ada di XAMPP, Laragon, dll.)
* **PHP** (Versi 7.4 atau yang lebih baru)
* **MySQL / MariaDB**

---

## 🚀 Panduan Instalasi & Penggunaan

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di komputer lokal Anda:

### 1. Clone Repository & Import Database (Manual Terminal)
Buka Terminal atau Command Prompt di komputer Anda, lalu jalankan perintah berikut secara berurutan:

```bash
# Clone repositori ke direktori web server (misalnya htdocs)
git clone [https://github.com/kiritokun-id/iseng-ip-scanner.git](https://github.com/kiritokun-id/iseng-ip-scanner.git)

# Masuk ke folder proyek
cd iseng-ip-scanner

# Masuk ke MySQL / MariaDB (sesuaikan username jika bukan root)
mysql -u root -p

# Di dalam prompt MySQL, buat database baru dan keluar:
CREATE DATABASE ipam_db;
exit;

# Import file ipam_db.sql ke dalam database
mysql -u root -p ipam_db < ipam_db.sql
```
(Masukkan password database Anda jika diminta. Jika tidak menggunakan password, Anda bisa langsung menekan Enter).

### 2. Konfigurasi Koneksi Database
Sesuaikan pengaturan koneksi database pada file db.php Anda jika diperlukan:
```php
$host = "localhost";
$user = "root";       // Sesuaikan username database anda
$pass = "";           // Sesuaikan password database anda
$db   = "ipam_db";    // Sesuaikan nama database anda
```
### 3. Jalankan Aplikasi
Buka browser Anda dan akses melalui alamat:

```
http://localhost/iseng-ip-scanner/
```

### 📁 Struktur File Utama
- scanner.php - Fitur utama pemindai IP (Live Scanner IP).
- subnet.php - Pengelolaan blok subnet.
- organization.php - Manajemen informasi organisasi.
- db.php - File koneksi database.
- ipam_db.sql - Berkas cadangan struktur & data database.
- demo.png - Tangkapan layar tampilan aplikasi.
