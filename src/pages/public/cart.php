<?php
/*
FILE: src/pages/public/cart.php
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Halaman keranjang lengkap — daftar item, update qty, ringkasan, checkout
*/
$activeNav = 'catalog';
$pageTitle = 'Keranjang Belanja';
require SRC_PATH . '/includes/header.php';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="mb-8" data-aos="fade-up">
    <h1 class="font-display text-3xl sm:text-4xl text-heading">Keranjang Belanja</h1>
    <p class="text-body mt-1">Periksa pesanan Anda sebelum melanjutkan ke checkout.</p>
  </div>

  <div class="grid lg:grid-cols-3 gap-8">
    <!-- NAILA YODA - Daftar item (diisi oleh cart.js -> #fullCartItems) -->
    <div id="fullCartItems" class="lg:col-span-2 space-y-4">
      <div class="bg-card border border-line rounded-2xl p-12 text-center shadow-soft">
        <div class="skeleton w-16 h-16 rounded-full mx-auto mb-4"></div>
        <p class="text-body">Memuat keranjang…</p>
      </div>
    </div>

    <!-- NAILA YODA - Ringkasan (#fullCartSummary) -->
    <aside id="fullCartSummary" class="hidden">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
        <h2 class="font-display text-xl text-heading mb-5">Ringkasan Pesanan</h2>
        <div class="space-y-3 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-body">Subtotal</span>
            <span id="summarySubtotal" class="font-semibold text-heading">Rp 0</span>
          </div>
          <div class="flex items-center justify-between text-body/70">
            <span>Ongkir</span>
            <span class="text-xs italic">Dihitung saat checkout</span>
          </div>
          <div class="border-t border-line pt-3 flex items-center justify-between">
            <span class="font-medium text-heading">Total</span>
            <span id="summaryTotal" class="font-display text-2xl text-primary">Rp 0</span>
          </div>
        </div>
        <a href="<?= url('checkout') ?>"
           class="mt-6 block text-center w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft hover:scale-[1.02]">
          Lanjut ke Checkout <i class="fa-solid fa-arrow-right ml-1"></i>
        </a>
        <a href="<?= url('catalog') ?>" class="mt-3 block text-center text-sm text-accent hover:text-hover transition">
          <i class="fa-solid fa-arrow-left mr-1"></i> Lanjut belanja
        </a>
      </div>
    </aside>
  </div>
</section>
<?php require SRC_PATH . '/includes/footer.php'; ?>
