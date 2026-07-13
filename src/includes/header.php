<?php
/*
FILE: src/includes/header.php
RESPONSIBLE MEMBER: HYUGA
FEATURE: Navbar, head (Tailwind config + fonts + library), cart badge & mini-cart
*/
$pageTitle  = $pageTitle ?? null;
$storeName  = setting('store_name', 'Lumiere Cookies');
$tagline    = setting('tagline', 'Freshly Baked Happiness');
$waPrimary  = whatsapp_primary();
$activeNav  = $activeNav ?? '';
$cartCount  = cart_count();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ? "$pageTitle — $storeName" : "$storeName — $tagline") ?></title>
<link rel="icon" type="image/png" href="<?= asset('images/logo.png') ?>">
<meta name="description" content="<?= e($storeName . ' — ' . $tagline) ?>">

<!-- HYUGA - Tailwind CSS via CDN + konfigurasi palet warna -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        cream:     '#FBF6EF',
        card:      '#FFFFFF',
        muted:     '#F5EBE0',
        primary:   '#A47551',
        hover:     '#8C6244',
        secondary: '#DDBEA9',
        accent:    '#CB997E',
        line:      '#EADFD6',
        heading:   '#2C2018',
        body:      '#6B5E54',
        success:   '#6FA773',
        warning:   '#E0A852',
        danger:    '#D9776A',
      },
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        sans:    ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        soft:  '0 1px 2px rgba(44,32,24,.04), 0 8px 24px -8px rgba(44,32,24,.12)',
        lift:  '0 10px 40px -12px rgba(140,98,68,.35)',
        glass: '0 8px 32px rgba(44,32,24,.10)',
      },
      borderRadius: { xl2: '1.25rem' },
    }
  }
}
</script>

<!-- HYUGA - Fonts: Playfair Display (heading) + Poppins (body) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<!-- AOS Animation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
<!-- Swiper -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.1.4/swiper-bundle.min.css">

<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script>window.APP_BASE = <?= json_encode(APP_BASE) ?>;</script>
</head>
<body class="bg-cream text-body font-sans antialiased selection:bg-secondary/60">

<!-- HYUGA - Top announcement bar -->
<div class="bg-heading text-cream text-center text-[13px] py-2 px-4 tracking-wide">
  <i class="fa-solid fa-cookie-bite mr-1 text-warning"></i>
  Dipanggang segar setiap hari di Surakarta — gratis ucapan custom untuk hampers ✦
</div>

<!-- HYUGA - Navbar -->
<header id="navbar" class="sticky top-0 z-40 transition-all duration-300">
  <nav class="backdrop-blur-md bg-cream/80 border-b border-line/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-[68px]">
        <!-- Logo -->
        <a href="<?= url('home') ?>" class="flex items-center gap-2.5 group">
          <span class="grid place-items-center w-11 h-11 rounded-full bg-card border border-line shadow-soft overflow-hidden group-hover:scale-105 transition">
            <img src="<?= asset('images/logo.png') ?>" alt="Lumiere Cookies" class="w-full h-full object-contain">
          </span>
          <span class="leading-tight">
            <span class="block font-display text-xl text-heading">Lumiere</span>
            <span class="block text-[10px] tracking-[0.3em] uppercase text-accent -mt-1">Cookies</span>
          </span>
        </a>

        <!-- Menu desktop -->
        <ul class="hidden md:flex items-center gap-1 text-[15px] font-medium">
          <?php
          $nav = [
            'home'     => ['Beranda', url('home')],
            'catalog'  => ['Katalog', url('catalog')],
            'articles' => ['Artikel', url('articles')],
            'contact'  => ['Kontak', url('contact')],
          ];
          foreach ($nav as $key => [$label, $href]):
            $is = $activeNav === $key; ?>
            <li>
              <a href="<?= $href ?>" class="px-4 py-2 rounded-full transition hover:bg-muted <?= $is ? 'text-heading bg-muted' : 'text-body' ?>"><?= $label ?></a>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- Aksi kanan -->
        <div class="flex items-center gap-2">
          <?php if ($waPrimary): ?>
          <a href="<?= e(wa_link($waPrimary, 'Halo Lumiere Cookies, saya ingin bertanya.')) ?>" target="_blank" rel="noopener"
             class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-success border border-success/30 hover:bg-success hover:text-white px-3.5 py-2 rounded-full transition">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp
          </a>
          <?php endif; ?>

          <!-- HYUGA - Cart button + floating badge bulat realtime -->
          <button id="cartButton" type="button" aria-label="Keranjang"
            class="relative grid place-items-center w-11 h-11 rounded-full bg-card border border-line hover:border-primary hover:shadow-soft transition group">
            <i class="fa-solid fa-bag-shopping text-heading group-hover:scale-110 transition"></i>
            <span id="cartBadge"
              class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 grid place-items-center rounded-full bg-primary text-cream text-[11px] font-semibold shadow-lift <?= $cartCount > 0 ? '' : 'hidden' ?>">
              <?= (int) $cartCount ?>
            </span>
          </button>

          <!-- Toggle menu mobile -->
          <button id="navToggle" type="button" aria-label="Menu"
            class="md:hidden grid place-items-center w-11 h-11 rounded-full bg-card border border-line text-heading">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Menu mobile -->
    <div id="mobileMenu" class="md:hidden hidden border-t border-line/70 bg-cream/95 backdrop-blur">
      <ul class="px-4 py-3 space-y-1 text-[15px] font-medium">
        <?php foreach ($nav as $key => [$label, $href]): ?>
          <li><a href="<?= $href ?>" class="block px-4 py-2.5 rounded-xl hover:bg-muted <?= $activeNav === $key ? 'bg-muted text-heading' : '' ?>"><?= $label ?></a></li>
        <?php endforeach; ?>
        <?php if ($waPrimary): ?>
        <li><a href="<?= e(wa_link($waPrimary, 'Halo Lumiere Cookies!')) ?>" target="_blank" rel="noopener" class="block px-4 py-2.5 rounded-xl text-success hover:bg-muted"><i class="fa-brands fa-whatsapp mr-2"></i>Chat WhatsApp</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
</header>

<!-- HYUGA - Mini Cart Drawer -->
<div id="cartOverlay" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-heading/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" data-cart-close></div>
  <aside id="cartDrawer"
    class="absolute right-0 top-0 h-full w-full max-w-[400px] bg-cream shadow-glass translate-x-full transition-transform duration-300 ease-out flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-line">
      <h3 class="font-display text-xl text-heading flex items-center gap-2">
        <i class="fa-solid fa-bag-shopping text-primary"></i> Keranjang
      </h3>
      <button type="button" data-cart-close class="w-9 h-9 grid place-items-center rounded-full hover:bg-muted text-body transition">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div id="cartItems" class="flex-1 overflow-y-auto px-5 py-4 space-y-3"></div>
    <div id="cartFooter" class="border-t border-line px-5 py-4 space-y-3">
      <div class="flex items-center justify-between text-sm">
        <span class="text-body">Subtotal</span>
        <span id="cartSubtotal" class="font-semibold text-heading text-lg">Rp 0</span>
      </div>
      <a href="<?= url('checkout') ?>" id="cartCheckoutBtn"
         class="block text-center w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft">
        Checkout <i class="fa-solid fa-arrow-right ml-1"></i>
      </a>
      <a href="<?= url('cart') ?>" class="block text-center text-sm text-accent hover:text-hover transition">Lihat keranjang lengkap</a>
    </div>
  </aside>
</div>

<main>
