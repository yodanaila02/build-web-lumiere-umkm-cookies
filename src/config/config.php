<?php
/*
FILE: src/config/config.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Konfigurasi global aplikasi (base path, info toko, palet warna)
*/

// FRIZA - Tampilkan error saat development (matikan di produksi)
error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('Asia/Jakarta');

// FRIZA - Base path aplikasi. Kosong jika di root domain.
// Bisa di-override lewat .env (APP_BASE). Dipakai semua URL & aset.
if (!defined('APP_BASE')) {
    define('APP_BASE', rtrim((string) env('APP_BASE', ''), '/'));
}

// FRIZA - Direktori penting
define('ROOT_PATH', dirname(__DIR__, 2));
define('SRC_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PRODUCTS', PUBLIC_PATH . '/assets/images/products');
define('UPLOAD_GALLERY', PUBLIC_PATH . '/assets/images/gallery');
define('UPLOAD_ARTICLES', PUBLIC_PATH . '/assets/images/articles');
define('UPLOAD_BANNERS', PUBLIC_PATH . '/assets/images/banners');

// FRIZA - Palet warna premium (dipakai juga di Tailwind config di header)
const PALETTE = [
    'background' => '#FBF6EF',
    'card'       => '#FFFFFF',
    'muted'      => '#F5EBE0',
    'primary'    => '#A47551',
    'hover'      => '#8C6244',
    'secondary'  => '#DDBEA9',
    'accent'     => '#CB997E',
    'border'     => '#EADFD6',
    'heading'    => '#2C2018',
    'text'       => '#6B5E54',
    'success'    => '#6FA773',
    'warning'    => '#E0A852',
    'danger'     => '#D9776A',
];
