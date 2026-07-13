<?php
/*
FILE: public/index.php
RESPONSIBLE MEMBER: HYUGA
FEATURE: Front controller — titik masuk tunggal + routing berbasis ?page=
*/

session_start();

require __DIR__ . '/../src/config/database.php';   // menyediakan env(), db()
require __DIR__ . '/../src/config/config.php';      // konstanta & palet
require __DIR__ . '/../src/includes/functions.php'; // helper bersama

// Peta route -> file di src/pages. Hanya route yang terdaftar yang boleh diakses.
$routes = [
    // Publik (HYUGA / HYUGA / NAILA)
    'home'          => 'public/home.php',
    'catalog'       => 'public/catalog.php',
    'product'       => 'public/product.php',
    'cart'          => 'public/cart.php',
    'checkout'      => 'public/checkout.php',
    'order-success' => 'public/order_success.php',
    'articles'      => 'public/articles.php',
    'article'       => 'public/article.php',
    'contact'       => 'public/contact.php',

    // Autentikasi (HYUGA)
    'login'  => 'auth/login.php',
    'logout' => 'auth/logout.php',

    // API AJAX (HYUGA - cart)
    'api/cart' => 'api/cart.php',

    // Admin (gabungan tim) — semua dilindungi require_admin() di dalam file
    'admin'             => 'admin/dashboard.php',
    'admin/products'    => 'admin/products.php',
    'admin/categories'  => 'admin/categories.php',
    'admin/orders'      => 'admin/orders.php',
    'admin/order'       => 'admin/order_detail.php',
    'admin/export'      => 'admin/export_orders.php',
    'admin/testimonials'=> 'admin/testimonials.php',
    'admin/faqs'        => 'admin/faqs.php',
    'admin/articles'    => 'admin/articles.php',
    'admin/gallery'     => 'admin/gallery.php',
    'admin/messages'    => 'admin/messages.php',
    'admin/settings'    => 'admin/settings.php',
];

$page = $_GET['page'] ?? 'home';

if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'home';                 // fallback aman ke beranda
    $notFound = true;
}

$file = SRC_PATH . '/pages/' . $routes[$page];
if (!is_file($file)) {
    http_response_code(500);
    die('Halaman tidak ditemukan: ' . e($page));
}

require $file;
