<?php
/*
FILE: src/includes/admin_header.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Layout panel admin — sidebar, topbar, proteksi require_admin()
*/
require_admin(); // FRIZA - semua halaman admin wajib login

$admin     = current_admin();
$adminPage = $adminPage ?? '';
$pageTitle = $pageTitle ?? 'Admin';

// FRIZA - Item menu sidebar
$menu = [
    ['admin',              'Dashboard',   'fa-gauge-high'],
    ['admin/products',     'Produk',      'fa-cookie-bite'],
    ['admin/categories',   'Kategori',    'fa-tags'],
    ['admin/orders',       'Pesanan',     'fa-receipt'],
    ['admin/articles',     'Artikel',     'fa-newspaper'],
    ['admin/gallery',      'Galeri',      'fa-images'],
    ['admin/testimonials', 'Testimoni',   'fa-comment-dots'],
    ['admin/faqs',         'FAQ',         'fa-circle-question'],
    ['admin/messages',     'Pesan Masuk', 'fa-envelope'],
    ['admin/settings',     'Pengaturan',  'fa-gear'],
];

$flashSuccess = get_flash('success');
$flashError   = get_flash('error');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — Admin Lumiere Cookies</title>
<link rel="icon" type="image/png" href="<?= asset('images/logo.png') ?>">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: { cream:'#FBF6EF', card:'#FFFFFF', muted:'#F5EBE0', primary:'#A47551', hover:'#8C6244',
            secondary:'#DDBEA9', accent:'#CB997E', line:'#EADFD6', heading:'#2C2018', body:'#6B5E54',
            success:'#6FA773', warning:'#E0A852', danger:'#D9776A' },
  fontFamily: { display:['"Playfair Display"','serif'], sans:['Poppins','ui-sans-serif','system-ui','sans-serif'] },
  boxShadow: { soft:'0 1px 2px rgba(44,32,24,.04), 0 8px 24px -8px rgba(44,32,24,.12)' },
}}}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-cream text-body font-sans antialiased">
<div class="min-h-screen flex">
  <!-- FRIZA - Sidebar -->
  <aside id="adminSidebar" class="fixed lg:sticky top-0 z-40 h-screen w-64 shrink-0 bg-heading text-cream/80 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="px-6 py-5 border-b border-cream/10 flex items-center gap-2">
      <span class="grid place-items-center w-9 h-9 rounded-full bg-cream overflow-hidden"><img src="<?= asset('images/logo.png') ?>" alt="Lumiere" class="w-full h-full object-contain"></span>
      <span class="font-display text-lg text-cream">Lumiere Admin</span>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
      <?php foreach ($menu as [$key, $label, $icon]):
        $is = $adminPage === $key; ?>
        <a href="<?= url($key) ?>"
           class="admin-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition <?= $is ? 'active bg-primary text-cream' : 'hover:bg-cream/10' ?>">
          <i class="fa-solid <?= $icon ?> w-5 text-center"></i><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="px-3 py-4 border-t border-cream/10">
      <a href="<?= url('home') ?>" target="_blank" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm hover:bg-cream/10 transition">
        <i class="fa-solid fa-globe w-5 text-center"></i>Lihat Situs
      </a>
      <a href="<?= url('logout') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-danger/90 hover:bg-danger/15 transition">
        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>Logout
      </a>
    </div>
  </aside>

  <!-- Overlay mobile -->
  <div id="sidebarOverlay" class="fixed inset-0 z-30 bg-heading/50 backdrop-blur-sm hidden lg:hidden"></div>

  <!-- Konten -->
  <div class="flex-1 min-w-0 flex flex-col">
    <!-- Topbar -->
    <header class="sticky top-0 z-20 bg-cream/85 backdrop-blur border-b border-line">
      <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <button id="sidebarToggle" class="lg:hidden w-10 h-10 grid place-items-center rounded-xl border border-line text-heading">
            <i class="fa-solid fa-bars"></i>
          </button>
          <h1 class="font-display text-xl sm:text-2xl text-heading"><?= e($pageTitle) ?></h1>
        </div>
        <div class="flex items-center gap-3">
          <span class="hidden sm:flex items-center gap-2 text-sm">
            <span class="w-9 h-9 grid place-items-center rounded-full bg-primary text-cream"><i class="fa-solid fa-user"></i></span>
            <span class="text-heading font-medium"><?= e($admin['name'] ?? 'Admin') ?></span>
          </span>
        </div>
      </div>
    </header>

    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
      <?php if ($flashSuccess): ?>
        <div class="mb-5 rounded-xl border border-success/30 bg-success/10 text-success px-4 py-3 text-sm">
          <i class="fa-solid fa-circle-check mr-2"></i><?= e($flashSuccess) ?>
        </div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div class="mb-5 rounded-xl border border-danger/30 bg-danger/10 text-danger px-4 py-3 text-sm">
          <i class="fa-solid fa-circle-exclamation mr-2"></i><?= e($flashError) ?>
        </div>
      <?php endif; ?>
