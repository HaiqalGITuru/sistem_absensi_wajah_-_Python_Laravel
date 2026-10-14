# Sistem Absensi Wajah — Diskominfo Kabupaten Tabalong

Sistem presensi karyawan berbasis **pengenalan wajah**. Terdiri dari dua bagian:

| Folder | Teknologi | Fungsi |
|---|---|---|
| `absensi-wajah-python` | Python / Flask | Inti sistem: kamera kios, pengenalan wajah, anti-foto, aturan absensi, database, dan **API** |
| `absensi-wajah-laravel` | Laravel / PHP | Panel admin: dashboard, kelola karyawan & jadwal, laporan — mengambil data lewat **API** ke sistem Python |

Panel Laravel tidak terhubung langsung ke database absensi untuk perubahan data;
semua lewat API. Sistem Python harus berjalan lebih dulu.

## Pemasangan

Panduan pemasangan lengkap dari awal (termasuk database kosong, kunci biometrik,
model AI, dan token API) ada di **`SERAH-TERIMA.md`** dan di folder
`absensi-wajah-python/docs/`.

## Catatan penting

- File `.env`, kunci biometrik, folder `vendor`, `.venv`, `model_files`, dan
  database **tidak** disertakan di repositori ini (lihat `.gitignore`). Semuanya
  dibuat ulang di komputer tujuan saat pemasangan.
- Isi database dimulai **kosong**; data karyawan & absensi diisi langsung di
  komputer kios saat pemakaian resmi.
