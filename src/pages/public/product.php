<?php
/*
FILE: src/pages/public/product.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: Detail produk — galeri, info, qty selector, add to cart, produk terkait
*/
$activeNav = 'catalog';

// NAYLA - Ambil produk berdasarkan id (JOIN kategori)
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1"
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Produk tidak ditemukan';
    require SRC_PATH . '/includes/header.php';
    echo '<section class="max-w-3xl mx-auto px-4 py-24 text-center">
            <i class="fa-solid fa-cookie-bite text-6xl text-line mb-6"></i>
            <h1 class="font-display text-3xl text-heading mb-3">Produk tidak ditemukan</h1>
            <p class="text-body mb-8">Maaf, produk yang Anda cari tidak tersedia atau sudah dihapus.</p>
            <a href="' . url('catalog') . '" class="inline-block bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">Kembali ke Katalog</a>
          </section>';
    require SRC_PATH . '/includes/footer.php';
    return;
}

$pageTitle = $product['name'];
$badge   = badge_meta($product['badge'] ?? 'none');
$soldOut = (int) $product['stock'] <= 0;
$waPrimary = whatsapp_primary();
$waText = "Halo Lumiere Cookies, saya tertarik dengan produk *{$product['name']}* (" . rupiah($product['price']) . "). Apakah masih tersedia?";

// NAYLA - Produk terkait (kategori sama, exclude diri sendiri)
$rel = db()->prepare(
    "SELECT * FROM products WHERE category_id = ? AND id <> ? AND is_active = 1 ORDER BY created_at DESC LIMIT 4"
);
$rel->execute([$product['category_id'], $product['id']]);
$related = $rel->fetchAll();

require SRC_PATH . '/includes/header.php';
?>

<!-- NAYLA - Breadcrumb -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <nav class="text-sm text-body flex items-center gap-2 flex-wrap">
    <a href="<?= url('home') ?>" class="hover:text-primary transition">Beranda</a>
    <i class="fa-solid fa-chevron-right text-[10px] text-line"></i>
    <a href="<?= url('catalog') ?>" class="hover:text-primary transition">Katalog</a>
    <i class="fa-solid fa-chevron-right text-[10px] text-line"></i>
    <a href="<?= url('catalog', ['category' => $product['category_slug']]) ?>" class="hover:text-primary transition"><?= e($product['category_name']) ?></a>
    <i class="fa-solid fa-chevron-right text-[10px] text-line"></i>
    <span class="text-heading font-medium"><?= e($product['name']) ?></span>
  </nav>
</div>

<!-- NAYLA - Detail produk -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="grid lg:grid-cols-2 gap-10 lg:gap-14">
    <!-- Gambar -->
    <div data-aos="fade-right">
      <div class="relative aspect-square rounded-3xl overflow-hidden bg-muted border border-line shadow-soft">
        <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover">
        <?php if ($badge): ?>
          <span class="badge <?= $badge[1] ?> absolute top-4 left-4 shadow-soft"><?= e($badge[0]) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Info -->
    <div data-aos="fade-left">
      <a href="<?= url('catalog', ['category' => $product['category_slug']]) ?>"
         class="inline-block text-xs tracking-[0.25em] uppercase text-accent font-semibold mb-3"><?= e($product['category_name']) ?></a>
      <h1 class="font-display text-4xl text-heading leading-tight"><?= e($product['name']) ?></h1>
      <div class="mt-4 flex items-center gap-3">
        <span class="font-display text-3xl text-primary"><?= rupiah($product['price']) ?></span>
        <?php if ($soldOut): ?>
          <span class="badge bg-line text-body">Stok Habis</span>
        <?php else: ?>
          <span class="text-sm text-success"><i class="fa-solid fa-circle-check mr-1"></i>Stok tersedia (<?= (int) $product['stock'] ?>)</span>
        <?php endif; ?>
      </div>

      <p class="mt-6 text-body leading-relaxed whitespace-pre-line"><?= e($product['description'] ?: 'Belum ada deskripsi untuk produk ini.') ?></p>

      <!-- NAYLA - Qty selector + add to cart -->
      <div class="mt-8 flex flex-wrap items-center gap-3">
        <div class="flex items-center border border-line rounded-full overflow-hidden bg-card <?= $soldOut ? 'opacity-50 pointer-events-none' : '' ?>">
          <button type="button" id="qtyMinus" class="w-11 h-11 grid place-items-center hover:bg-muted transition"><i class="fa-solid fa-minus text-sm"></i></button>
          <input id="qtyInput" type="number" value="1" min="1" max="<?= max(1, (int) $product['stock']) ?>"
                 class="w-14 text-center font-semibold text-heading border-0 focus:ring-0 bg-transparent" readonly>
          <button type="button" id="qtyPlus" class="w-11 h-11 grid place-items-center hover:bg-muted transition"><i class="fa-solid fa-plus text-sm"></i></button>
        </div>

        <?php if ($soldOut): ?>
          <button disabled class="flex-1 min-w-[200px] bg-line text-body/60 font-semibold py-3 px-6 rounded-full cursor-not-allowed">
            <i class="fa-solid fa-ban mr-2"></i>Stok Habis
          </button>
        <?php else: ?>
          <button data-add-to-cart data-id="<?= (int) $product['id'] ?>" data-qty-input="#qtyInput"
            class="btn-primary flex-1 min-w-[200px] bg-primary hover:bg-hover text-cream font-semibold py-3 px-6 rounded-full transition shadow-soft hover:scale-[1.02]">
            <i class="fa-solid fa-bag-shopping mr-2"></i>Tambah ke Keranjang
          </button>
        <?php endif; ?>
      </div>

      <?php if ($waPrimary): ?>
      <a href="<?= e(wa_link($waPrimary, $waText)) ?>" target="_blank" rel="noopener"
         class="mt-4 inline-flex items-center gap-2 text-success font-medium hover:text-success/80 transition">
        <i class="fa-brands fa-whatsapp text-lg"></i> Tanya produk ini via WhatsApp
      </a>
      <?php endif; ?>

      <!-- Info tambahan -->
      <div class="mt-8 grid grid-cols-3 gap-3 text-center">
        <div class="bg-muted/60 rounded-2xl p-4">
          <i class="fa-solid fa-wheat-awn text-primary text-xl mb-2"></i>
          <p class="text-xs text-body">Bahan Premium</p>
        </div>
        <div class="bg-muted/60 rounded-2xl p-4">
          <i class="fa-solid fa-temperature-arrow-up text-primary text-xl mb-2"></i>
          <p class="text-xs text-body">Dipanggang Segar</p>
        </div>
        <div class="bg-muted/60 rounded-2xl p-4">
          <i class="fa-solid fa-box-open text-primary text-xl mb-2"></i>
          <p class="text-xs text-body">Kemasan Rapi</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NAYLA - Produk terkait -->
<?php if ($related): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
  <div class="flex items-end justify-between mb-6">
    <h2 class="font-display text-2xl sm:text-3xl text-heading">Produk Terkait</h2>
    <a href="<?= url('catalog', ['category' => $product['category_slug']]) ?>" class="text-sm text-accent hover:text-hover transition">Lihat semua <i class="fa-solid fa-arrow-right ml-1"></i></a>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <?php foreach ($related as $i => $rp) echo render_product_card($rp, (string) ($i * 80)); ?>
  </div>
</section>
<?php endif; ?>

<!-- NAYLA - Qty stepper logic -->
<script>
(function () {
  const input = document.getElementById('qtyInput');
  const minus = document.getElementById('qtyMinus');
  const plus  = document.getElementById('qtyPlus');
  if (!input) return;
  const max = parseInt(input.getAttribute('max'), 10) || 99;
  minus && minus.addEventListener('click', () => {
    input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
  });
  plus && plus.addEventListener('click', () => {
    input.value = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
  });
})();
</script>

<?php require SRC_PATH . '/includes/footer.php'; ?>