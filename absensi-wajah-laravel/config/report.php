<?php

/**
 * Pengaturan kop surat & tanda tangan untuk PDF laporan.
 * Isi lewat .env, atau ubah nilai default di sini.
 */
return [
    // Baris kop (baris pertama kecil, baris kedua tebal & besar)
    'org_line1' => env('REPORT_ORG_LINE1', 'NAMA INSTANSI / PERUSAHAAN'),
    'org_line2' => env('REPORT_ORG_LINE2', 'UNIT / DIVISI'),
    'address'   => env('REPORT_ADDRESS', 'Alamat lengkap instansi'),
    'contact'   => env('REPORT_CONTACT', 'Telepon / Email / Website'),

    // Logo opsional, path relatif terhadap folder public/ (mis. 'images/logo.png'). Kosongkan jika tidak ada.
    'logo' => env('REPORT_LOGO', ''),

    // Tanda tangan
    'city'           => env('REPORT_CITY', 'Kota'),
    'signer_title'   => env('REPORT_SIGNER_TITLE', 'Kepala'),
    'signer_name'    => env('REPORT_SIGNER_NAME', ''),
    'signer_id'      => env('REPORT_SIGNER_ID', ''),
];
