# CITA SISWA - Platform Beasiswa & Prestasi Siswa

> **Cita Siswa** adalah platform web terpadu yang dirancang untuk membantu siswa dan pelajar menemukan peluang beasiswa, informasi kompetisi/lomba, serta bimbingan review berkas (CV dan esai) bersama mentor/alumni berprestasi.

---

## 📌 Tentang Aplikasi

Cita Siswa menghubungkan pelajar dengan berbagai kesempatan pendidikan dan pendampingan:
- **Cari Beasiswa**: Direktori lengkap beasiswa tingkat daerah hingga nasional dan internasional dengan fitur filter kriteria.
- **Info Lomba**: Katalog kompetisi akademik dan non-akademik untuk mengasah potensi dan portofolio prestasi siswa.
- **Bimbingan Alumni & Mentor**: Siswa dapat memilih mentor terdaftar dan mengajukan ulasan berkas aplikasi (CV/Motivation Letter).
- **Dashboard Siswa**: Memantau progres lamaran beasiswa dan status permintaan ulasan mentor.
- **Panel Admin**: Pengelolaan data beasiswa, lomba, verifikasi mentor, data siswa, hingga analitik funnel pelamar.
- **Panel Mentor**: Tempat mentor menerima, mengulas, dan memberikan feedback berkas pengajuan siswa.

---

## 👥 Akun Demo Siap Pakai

Database bawaan telah dilengkapi akun contoh untuk memudahkan pengujian semua peran (*role*):

| Peran (*Role*) | Email | Password | Akses & Fitur Utama |
| :--- | :--- | :--- | :--- |
| **Siswa / Pelajar** | `siswa@berasiswa.test` | `siswa123` | Jelajah Beasiswa & Lomba, Ajukan Review ke Mentor, Dashboard Siswa, Riwayat Pendaftaran, Pengaturan Profil |
| **Mentor** | `mentor@berasiswa.test` | `ganti-password-mentor` | Dashboard Mentor, Daftar Permintaan Review Siswa, Feedback Ulasan Berkas, Profil Mentor |
| **Admin** | `admin@berasiswa.test` | `ganti-password-admin` | Dashboard Admin, Manajemen Konten Beasiswa & Lomba, Verifikasi Mentor & Siswa, Tracking Funnel |
| **Tamu / Publik** | *(Tanpa Login)* | - | Halaman Landing / Sambutan Pengenalan Program |

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: SQLite (bawaan lokal) / MySQL kompatibel
- **Frontend**: Blade Templating, Tailwind CSS, Vite
- **Autentikasi**: Laravel Auth bawaan dengan Role-Based Access Control (Admin, Mentor, User)

---

## 💻 Panduan Menjalankan di Komputer Lokal

1. **Clone Repository**:
   ```bash
   git clone https://github.com/Randikaarief/cita-siswa.git
   cd cita-siswa
   ```

2. **Instal Dependensi Backend (Composer)**:
   ```bash
   composer install
   ```

3. **Salin Berkas Lingkungan (`.env`)**:
   ```bash
   cp .env.example .env
   # Atau pada Windows PowerShell:
   # Copy-Item .env.example .env
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database & Seeder**:
   ```bash
   php artisan migrate --seed
   ```
   *(Perintah ini akan membuat database SQLite beserta data contoh beasiswa, lomba, profil mentor, dan akun demo di atas).*

6. **Instal Dependensi Frontend & Kompilasi Aset**:
   ```bash
   npm install
   npm run build
   ```

7. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Buka browser di: **http://127.0.0.1:8000**

---

## ☁️ Catatan Deployment ke Vercel

Repositori ini sudah dilengkapi konfigurasi `vercel.json` dan folder `api/index.php` untuk mendukung deployment di Vercel:

1. **Environment Variables di Dashboard Vercel**:
   Pastikan mengisi variabel lingkungan berikut di menu *Settings > Environment Variables*:
   - `APP_NAME`: `Cita Siswa`
   - `APP_ENV`: `production`
   - `APP_KEY`: *(Salin nilai APP_KEY dari berkas .env lokal Anda)*
   - `APP_DEBUG`: `false`
   - `APP_URL`: `https://nama-projek-anda.vercel.app`
   - `SESSION_DRIVER`: `cookie`
   - `CACHE_STORE`: `array`
   - `LOG_CHANNEL`: `stderr`

2. **Catatan Penting mengenai Database di Vercel**:
   - Vercel menggunakan arsitektur *Serverless Function* dengan sistem file *read-only* (hanya folder `/tmp` yang dapat ditulisi secara sementara).
   - Database file lokal seperti SQLite akan ter-reset setiap kali fungsi serverless mengalami *cold restart*.
   - Untuk data permanen saat online, hubungkan aplikasi ke database cloud MySQL atau PostgreSQL gratis (misalnya dari **Supabase**, **Neon**, **TiDB Cloud**, atau **Railway/Aiven**), lalu atur variabel `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di Vercel.

---

## 📄 Lisensi

Proyek ini bersifat open-source di bawah lisensi [MIT](LICENSE).
