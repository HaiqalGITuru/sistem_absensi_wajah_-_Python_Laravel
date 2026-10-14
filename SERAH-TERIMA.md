# Panduan Serah Terima — Sistem Absensi Wajah Diskominfo Tabalong

Dokumen ini untuk pembimbing/penerima proyek, berisi daftar berkas yang
diserahkan dan langkah memasang sistem di komputer baru dari awal.

**Catatan penting:** sistem ini diserahkan **dengan database kosong**. Data
pegawai dan absensi selama pengembangan adalah DATA UJI dan sengaja TIDAK
diserahkan. Data pegawai sungguhan diisi langsung di komputer ini saat
pemasangan (bagian 5).

Sistem terdiri atas **dua bagian**:

| Bagian | Teknologi | Folder |
|---|---|---|
| Inti sistem (kamera, pengenalan wajah, absensi, API) | Python / Flask | `absensi-wajah-kantor` |
| Panel admin (dashboard, laporan) | Laravel | `absensi-wajah` |

Panel Laravel **tidak** terhubung langsung ke database absensi. Semua data
diambil lewat API ke sistem Python. Jadi sistem Python harus berjalan dulu.

---

## 1. Berkas yang diserahkan

| Berkas | Keterangan |
|---|---|
| Folder proyek Python (`absensi-wajah-kantor`) | Tanpa `.env`, `.venv`, dan `model_files` (diunduh ulang) |
| Folder proyek Laravel (`absensi-wajah`) | Tanpa `.env` dan `vendor` (dipasang ulang) |

> Database **tidak** diserahkan karena dimulai dari kosong (dibuat di bagian
> 3.2). File kunci biometrik juga **tidak** diserahkan — kunci baru dibuat di
> komputer ini (bagian 3.4), supaya data resmi memakai kunci yang belum pernah
> ada di tempat lain.

---

## 2. Yang perlu dipasang lebih dulu

1. **Python 3.11** (64-bit) — centang *Add python.exe to PATH* saat memasang.
2. **Laragon** (atau XAMPP) untuk MySQL dan phpMyAdmin. Panduan ini memakai
   **Laragon**. Jalankan hanya satu server MySQL (jangan Laragon dan XAMPP
   bersamaan, karena sama-sama memakai port 3306).
3. **Git for Windows**.
4. **Composer** (sudah termasuk di Laragon) untuk Laravel.
5. **Microsoft Edge** (sudah ada di Windows) untuk tampilan kios.
6. **Kamera** Logitech C270 (atau kamera lain yang didukung).

---

## 3. Memasang sistem Python (inti)

Semua perintah dijalankan di folder `absensi-wajah-kantor`.

### 3.1 Siapkan lingkungan
```powershell
python -m venv .venv
.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```
Jika PowerShell menolak menjalankan `Activate.ps1`, jalankan sekali:
`Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`.

### 3.2 Buat database kosong
1. Nyalakan **Laragon → Start All**. Pastikan MySQL *started*.
2. Buka `http://localhost/phpmyadmin`, login sebagai `root`
   (password Laragon biasanya kosong).
3. Buat database baru `absensi_wajah` dengan collation `utf8mb4_unicode_ci`.
   **Biarkan kosong** — jangan impor apa pun.

### 3.3 Buat pengguna database `absensi_app`
1. phpMyAdmin → **User accounts** → **Add user account**.
2. User name: `absensi_app`, Host name: **Local** (`localhost`).
3. Isi password (ingat baik-baik — harus sama dengan `DB_PASSWORD` di `.env`).
   Jangan centang apa pun di **Global privileges**.
4. Klik **Go**.
5. Buka tab **SQL**, jalankan:
   ```sql
   GRANT ALL PRIVILEGES ON absensi_wajah.* TO 'absensi_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

> **Alternatif cepat untuk sekadar menjalankan:** lewati langkah 3.3 dan pakai
> `root` di `.env` (`DB_USER=root`, `DB_PASSWORD=` kosong). Kurang aman, tetapi
> cukup untuk peninjauan.

### 3.4 Buat file `.env`
Salin `.env.example` menjadi `.env`, lalu sesuaikan nilai berikut:

| Kunci | Isi di komputer baru |
|---|---|
| `APP_ENV` | `production` untuk pemakaian resmi (atau `development` saat menyiapkan) |
| `SECRET_KEY` | hasil `python -c "import secrets; print(secrets.token_hex(32))"` |
| `DB_USER` / `DB_PASSWORD` | `absensi_app` dan passwordnya (langkah 3.3) |
| `DB_NAME` | `absensi_wajah` |
| `OFFICE_TIMEZONE` | `Asia/Makassar` |
| `BIOMETRIC_KEY_FILE` | lokasi file kunci di luar proyek, mis. `D:\absensi-secrets\biometric_keys.json` |
| `BACKUP_DIR` | folder backup, mis. `D:\absensi-backup` |
| `CAMERA_NAME` | `C270` (atau nama kamera di komputer ini — lihat 3.7) |
| `FACE_MATCH_THRESHOLD` | `0.60` |
| `LIVENESS_MODE` | `passive+challenge` |

### 3.5 Siapkan struktur dan kunci
```powershell
flask --app app db upgrade          # membuat semua tabel (kosong)
flask --app app models-download     # unduh model AI (±41 MB)
flask --app app models-check        # harus 4 baris OK
flask --app app biometric-keygen    # membuat kunci enkripsi BARU (sekali saja)
flask --app app biometric-status    # wajar menampilkan 0 templat (masih kosong)
```

### 3.6 Periksa kamera
```powershell
flask --app app camera-scan         # daftar kamera beserta namanya
flask --app app camera-check --seconds 10
```
Sesuaikan `CAMERA_NAME` di `.env` dengan kamera yang benar (pastikan C270,
bukan kamera laptop).

### 3.7 Jalankan
Dua jendela terpisah:
```powershell
scripts\jalankan_server.bat         # server web + API (port 5000)
scripts\jalankan_kios.bat           # layanan kamera (mencatat absensi)
```
Buka `http://127.0.0.1:5000/api/health` — harus tampil `"status": "ok"`.

---

## 4. Memasang panel Laravel

Semua perintah dijalankan di folder `absensi-wajah` (pakai Terminal Laragon).

### 4.1 Pasang kebutuhan
```
composer install
```

### 4.2 Siapkan `.env`
Salin `.env.example` menjadi `.env`, lalu atur:
```
DB_CONNECTION=sqlite
DB_DATABASE=C:\laragon\www\absensi-wajah\database\database.sqlite
PYTHON_API_URL=http://127.0.0.1:5000
PYTHON_API_TOKEN=   (lihat langkah 4.4)
```
Pastikan ekstensi SQLite aktif: `php -m | findstr sqlite` harus menampilkan
`pdo_sqlite` dan `sqlite3`. Jika belum, aktifkan di `php.ini` Laragon.

### 4.3 Siapkan database Laravel (kosong)
```
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate
php artisan admin:create admin --name="Administrator"
```
`admin:create` akan meminta password minimal 12 karakter. Ini akun login
panel Laravel, terpisah dari admin sistem Python.

### 4.4 Buat token API untuk Laravel
Jalankan di **proyek Python**:
```powershell
flask --app app api-token-create --name "Laravel - panel admin" --scope write
```
Salin token (diawali `aw_`) ke `PYTHON_API_TOKEN` di `.env` Laravel, lalu:
```
php artisan config:clear
```

### 4.5 Jalankan
```
php artisan serve
```
Buka `http://127.0.0.1:8000`, login dengan akun admin Laravel. Buka menu
**Status Python API** — keempat baris harus **OK**.

---

## 5. Mengisi data pegawai sungguhan

Database dimulai kosong, jadi data resmi diisi di sini. Urutannya:

1. **Jadwal kerja.** Admin sistem Python (`http://127.0.0.1:5000`) atau panel
   Laravel → menu **Jadwal Kerja** → buat jadwal resmi. **Sesuaikan jam kerja
   dengan aturan Diskominfo yang sebenarnya** (jam masuk, jam pulang per hari,
   toleransi terlambat). Jam di data uji dulu hanya contoh.
2. **Karyawan.** Tambah pegawai satu per satu, dengan **NIP sebagai kode**.
3. **Daftarkan wajah** tiap pegawai:
   - Lewat panel Laravel: centang "Daftarkan wajah di kamera kios" saat
     menambah, lalu pegawai berdiri di depan kamera; atau
   - Lewat terminal Python: `flask --app app face-enroll --employee NIP`.
4. Setelah menambah beberapa pegawai, jalankan
   `flask --app app face-similarity-report` untuk memastikan tidak ada wajah
   yang terlalu mirip sehingga berisiko tertukar.
5. Buat backup pertama data resmi: `flask --app app backup-create`, dan salin
   kunci ke media terpisah: `flask --app app backup-key E:\kunci-absensi`.

---

## 6. Daftar periksa setelah pemasangan

- [ ] `flask --app app models-check` → 4 model OK
- [ ] `flask --app app biometric-status` → kunci siap (templat 0 saat masih kosong)
- [ ] `http://127.0.0.1:5000/api/health` → `"status": "ok"`
- [ ] Panel Laravel → **Status Python API** semua OK
- [ ] Setelah data diisi: lewat di depan kamera → absensi tercatat di menu **Absensi**
- [ ] `flask --app app security-check` dijalankan sebelum dipakai resmi

---

## 7. Dokumentasi lengkap

Penjelasan lebih dalam ada di folder `docs/` proyek Python:

| Dokumen | Isi |
|---|---|
| `docs/INSTALASI.md` | Pemasangan lengkap di komputer kios |
| `docs/OPERASIONAL.md` | Rutinitas admin, pendaftaran wajah, penanganan gangguan |
| `docs/KEAMANAN.md` | Daftar periksa keamanan sebelum dipakai resmi |
| `docs/BACKUP.md` | Backup harian, pemulihan, pengosongan data uji |
| `docs/API.md` | API untuk panel admin |
| `docs/MODEL_DECISION.md` | Pemilihan model AI, lisensi, kalibrasi ambang |
| `docs/PERFORMANCE.md` | Hasil uji performa |
| `INTEGRASI.md` (proyek Laravel) | Cara panel Laravel terhubung ke API |

---

## 8. Catatan untuk pemakaian resmi

Rinciannya di `docs/KEAMANAN.md`:

1. **`SECRET_KEY` dan password database** diisi nilai baru yang belum pernah
   terlihat (langkah 3.3 dan 3.4). Jangan pakai nilai contoh.
2. **Persetujuan data wajah**: data biometrik termasuk data pribadi spesifik
   menurut UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi. Perlu
   pemberitahuan dan persetujuan pegawai, dikonfirmasi dengan bagian hukum.
3. **HTTPS** bila panel diakses lintas komputer di jaringan kantor.
4. **Uji coba berdampingan** 1–2 minggu dengan absensi lama sebelum resmi.
5. **Backup harian** dijadwalkan (lihat `docs/BACKUP.md`).
