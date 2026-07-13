<?php
/*
FILE: src/pages/public/home.php
RESPONSIBLE MEMBER: HYUGA
FEATURE: Landing page — hero, best seller, kategori, galeri, testimoni, FAQ, lokasi
*/
$activeNav = 'home';
$pageTitle = null;

// Data dari database (tidak di-hardcode)
$bestSellers = db()->query(
    "SELECT p.*, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND p.is_best_seller = 1
     ORDER BY p.created_at DESC LIMIT 4"
)->fetchAll();

$categories = db()->query(
    "SELECT c.*, COUNT(p.id) AS total
     FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
     GROUP BY c.id ORDER BY c.id"
)->fetchAll();

$newest = db()->query(
    "SELECT p.* FROM products p WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 8"
)->fetchAll();

$testimonials = db()->query(
    "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

$faqs = db()->query(
    "SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id"
)->fetchAll();

$gallery = db()->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 6")->fetchAll();

$mapsEmbed = setting('maps_embed');
$address   = setting('address');
$waPrimary = whatsapp_primary();

$catIcon = ['cookies' => 'fa-cookie-bite', 'hampers' => 'fa-gift', 'dessert-box' => 'fa-cake-candles'];

require SRC_PATH . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="relative overflow-hidden hero-mesh grain">
  <!-- bentuk dekoratif -->
  <div class="pointer-events-none absolute -top-24 -right-24 w-96 h-96 rounded-full bg-secondary/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -bottom-28 -left-20 w-80 h-80 rounded-full bg-accent/30 blur-3xl"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
    <div class="grid lg:grid-cols-2 gap-10 items-center py-16 lg:py-24">
      <!-- kiri -->
      <div data-aos="fade-right">
        <span class="inline-flex items-center gap-2 bg-card/70 backdrop-blur border border-line text-sm text-accent px-4 py-1.5 rounded-full shadow-soft">
          <i class="fa-solid fa-star text-warning"></i> Est. 2025 · Surakarta
        </span>
        <div class="flex items-center gap-6 mt-6">
          <div class="w-20 h-20 flex-shrink-0 hidden sm:block">
            <img src="<?= asset('images/logo.png') ?>" alt="Lumiere Cookies" class="w-full h-full object-contain">
          </div>
          <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl leading-[1.05] text-heading">
            Freshly Baked<br><span class="italic text-primary">Happiness</span>
          </h1>
        </div>
        <p class="text-lg text-body mt-6 max-w-md leading-relaxed">
          Kukis, hampers, dan dessert box yang dipanggang segar setiap hari. Setiap gigitan membawa kebahagiaan kecil dari dapur Lumiere.
        </p>
        <div class="flex flex-wrap items-center gap-3 mt-8">
          <a href="<?= url('catalog') ?>" class="btn-primary btn-shine inline-flex items-center gap-2 bg-primary hover:bg-hover text-cream font-semibold px-7 py-3.5 rounded-full shadow-lift">
            Lihat Katalog <i class="fa-solid fa-arrow-right"></i>
          </a>
          <?php if ($waPrimary): ?>
          <a href="<?= e(wa_link($waPrimary, 'Halo Lumiere Cookies, saya mau pesan.')) ?>" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-card border border-line hover:border-success text-heading font-medium px-7 py-3.5 rounded-full transition">
            <i class="fa-brands fa-whatsapp text-success"></i> Pesan via WA
          </a>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-8 mt-10">
          <div><p class="font-display text-3xl text-heading">11+</p><p class="text-sm text-body">Varian Produk</p></div>
          <div class="w-px h-10 bg-line"></div>
          <div><p class="font-display text-3xl text-heading">100%</p><p class="text-sm text-body">Fresh Baked</p></div>
          <div class="w-px h-10 bg-line"></div>
          <div><p class="font-display text-3xl text-heading">4.9★</p><p class="text-sm text-body">Rating Pembeli</p></div>
        </div>
      </div>

      <!-- kanan: kartu produk melayang -->
      <div class="relative h-[420px] hidden lg:block" data-aos="fade-left">
        <!-- Large logo on the right side of the hero -->
        <div class="absolute top-6 right-6 w-56 h-56 flex items-center justify-center opacity-95 rounded-full overflow-hidden bg-card p-4 border border-line shadow-soft">
          <img src="<?= asset('images/logo.png') ?>" alt="Lumiere Cookies" class="w-full h-full object-contain">
        </div>
        <div class="floaty absolute top-0 right-8 w-60 bg-card rounded-3xl shadow-glass border border-line p-3 rotate-3">
          <div class="aspect-square rounded-2xl bg-muted overflow-hidden">
            <img src="<?= e($bestSellers[0]['image'] ?? '') ? product_image($bestSellers[0]['image']) : asset('images/placeholder.svg') ?>" class="w-full h-full object-cover" alt="">
          </div>
          <p class="mt-2 px-1 font-medium text-heading text-sm"><?= e($bestSellers[0]['name'] ?? 'Choco Chunk Cookies') ?></p>
          <p class="px-1 text-primary font-display"><?= rupiah($bestSellers[0]['price'] ?? 28000) ?></p>
        </div>
        <div class="floaty-slow absolute bottom-2 left-2 w-52 bg-card rounded-3xl shadow-glass border border-line p-3 -rotate-3">
          <div class="aspect-square rounded-2xl bg-muted overflow-hidden">
            <img src="<?= e($bestSellers[1]['image'] ?? '') ? product_image($bestSellers[1]['image']) : asset('images/placeholder.svg') ?>" class="w-full h-full object-cover" alt="">
          </div>
          <p class="mt-2 px-1 font-medium text-heading text-sm"><?= e($bestSellers[1]['name'] ?? 'Hampers Klasik') ?></p>
          <p class="px-1 text-primary font-display"><?= rupiah($bestSellers[1]['price'] ?? 145000) ?></p>
        </div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-28 h-28 grid place-items-center rounded-full bg-heading text-cream shadow-lift floaty">
          <div class="text-center"><i class="fa-solid fa-cookie-bite text-2xl text-warning"></i><p class="text-[10px] mt-1 tracking-widest">LUMIERE</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== KATEGORI ===================== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="text-center mb-10" data-aos="fade-up">
    <p class="text-accent font-medium tracking-widest uppercase text-sm">Kategori</p>
    <h2 class="font-display text-4xl text-heading mt-2">Pilih Sesuai Selera</h2>
  </div>
  <div class="grid sm:grid-cols-3 gap-5">
    <?php foreach ($categories as $i => $cat): ?>
    <a href="<?= url('catalog', ['category' => $cat['slug']]) ?>"
       class="product-card group bg-card border border-line rounded-2xl p-7 shadow-soft flex items-center gap-4"
       data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
      <span class="w-14 h-14 grid place-items-center rounded-2xl bg-muted text-primary text-2xl group-hover:bg-primary group-hover:text-cream transition">
        <i class="fa-solid <?= $catIcon[$cat['slug']] ?? 'fa-cookie' ?>"></i>
      </span>
      <div class="flex-1">
        <h3 class="font-display text-xl text-heading"><?= e($cat['name']) ?></h3>
        <p class="text-sm text-body"><?= (int) $cat['total'] ?> produk</p>
      </div>
      <i class="fa-solid fa-arrow-right text-line group-hover:text-primary group-hover:translate-x-1 transition"></i>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ===================== BEST SELLER ===================== -->
<section class="bg-muted/60 py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-10" data-aos="fade-up">
      <div>
        <p class="text-accent font-medium tracking-widest uppercase text-sm">Favorit Pelanggan</p>
        <h2 class="font-display text-4xl text-heading mt-2">Best Seller</h2>
      </div>
      <a href="<?= url('catalog') ?>" class="link-underline hidden sm:inline-flex items-center gap-2 text-primary font-medium">Lihat semua <i class="fa-solid fa-arrow-right text-xs"></i></a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
      <?php foreach ($bestSellers as $i => $p) echo render_product_card($p, (string) ($i * 80)); ?>
    </div>
  </div>
</section>

<!-- ===================== KOLEKSI TERBARU ===================== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="text-center mb-10" data-aos="fade-up">
    <p class="text-accent font-medium tracking-widest uppercase text-sm">Katalog</p>
    <h2 class="font-display text-4xl text-heading mt-2">Koleksi Terbaru</h2>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
    <?php foreach ($newest as $i => $p) echo render_product_card($p, (string) (($i % 4) * 70)); ?>
  </div>
  <div class="text-center mt-10">
    <a href="<?= url('catalog') ?>" class="btn-primary btn-shine inline-flex items-center gap-2 bg-heading hover:bg-primary text-cream font-semibold px-7 py-3.5 rounded-full transition shadow-soft">
      Jelajahi Semua Produk <i class="fa-solid fa-arrow-right"></i>
    </a>
  </div>
</section>

<!-- ===================== GALERI ===================== -->
<?php if ($gallery): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="text-center mb-10" data-aos="fade-up">
    <p class="text-accent font-medium tracking-widest uppercase text-sm">Galeri</p>
    <h2 class="font-display text-4xl text-heading mt-2">Momen Manis Kami</h2>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
    <?php foreach ($gallery as $i => $g): ?>
    <div class="relative group overflow-hidden rounded-2xl aspect-[4/3] bg-muted shadow-soft <?= $i === 0 ? 'md:col-span-2 md:row-span-2 md:aspect-square' : '' ?>" data-aos="zoom-in" data-aos-delay="<?= $i*60 ?>">
      <img src="<?= gallery_image($g['image']) ?>" alt="<?= e($g['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
      <div class="absolute inset-0 bg-gradient-to-t from-heading/60 to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-4">
        <p class="text-cream font-medium"><?= e($g['title']) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ===================== TESTIMONI ===================== -->
<?php if ($testimonials): ?>
<section class="py-16 bg-muted/60 mt-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10" data-aos="fade-up">
      <p class="text-accent font-medium tracking-widest uppercase text-sm">Testimoni</p>
      <h2 class="font-display text-4xl text-heading mt-2">Kata Mereka</h2>
    </div>
    <div class="swiper testi-swiper pb-12" data-aos="fade-up">
      <div class="swiper-wrapper">
        <?php foreach ($testimonials as $t): ?>
        <div class="swiper-slide h-auto">
          <div class="bg-card border border-line rounded-2xl p-6 shadow-soft h-full flex flex-col">
            <div class="flex gap-1 text-warning mb-3">
              <?php for ($s = 0; $s < 5; $s++): ?>
                <i class="fa-<?= $s < (int) $t['rating'] ? 'solid' : 'regular' ?> fa-star text-sm"></i>
              <?php endfor; ?>
            </div>
            <p class="text-body leading-relaxed flex-1">“<?= e($t['content']) ?>”</p>
            <div class="flex items-center gap-3 mt-5">
              <span class="w-10 h-10 grid place-items-center rounded-full bg-primary text-cream font-display"><?= e(mb_substr($t['customer_name'], 0, 1)) ?></span>
              <span class="font-medium text-heading"><?= e($t['customer_name']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="testi-pagination swiper-pagination text-center mt-6"></div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== FAQ ===================== -->
<?php if ($faqs): ?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
  <div class="text-center mb-10" data-aos="fade-up">
    <p class="text-accent font-medium tracking-widest uppercase text-sm">FAQ</p>
    <h2 class="font-display text-4xl text-heading mt-2">Pertanyaan Umum</h2>
  </div>
  <div class="space-y-3">
    <?php foreach ($faqs as $i => $f): ?>
    <div data-faq class="bg-card border border-line rounded-2xl shadow-soft overflow-hidden" data-aos="fade-up" data-aos-delay="<?= $i*50 ?>">
      <button data-faq-toggle type="button" class="w-full flex items-center justify-between gap-4 text-left px-5 py-4">
        <span class="font-medium text-heading"><?= e($f['question']) ?></span>
        <i class="fa-solid fa-chevron-down faq-icon text-primary shrink-0"></i>
      </button>
      <div data-faq-body><p class="px-5 pb-5 text-body leading-relaxed"><?= e($f['answer']) ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ===================== LOKASI & KONTAK ===================== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="grid lg:grid-cols-2 gap-8 items-stretch">
    <div class="bg-heading text-cream rounded-3xl p-9 relative overflow-hidden grain" data-aos="fade-right">
      <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-primary/40 blur-2xl"></div>
      <p class="text-accent font-medium tracking-widest uppercase text-sm relative">Kunjungi Kami</p>
      <h2 class="font-display text-4xl mt-2 relative">Lumiere Cookies</h2>
      <p class="text-cream/75 mt-5 leading-relaxed relative"><i class="fa-solid fa-location-dot mr-2 text-accent"></i><?= e($address) ?></p>
      <?php if ($ig = setting('instagram')): ?>
      <p class="text-cream/75 mt-3 relative"><i class="fa-brands fa-instagram mr-2 text-accent"></i><?= e($ig) ?></p>
      <?php endif; ?>
      <?php foreach (whatsapp_numbers() as $wa): ?>
      <a href="<?= e(wa_link($wa['number'], 'Halo Lumiere Cookies!')) ?>" target="_blank" rel="noopener" class="relative inline-flex items-center gap-2 mt-3 mr-2 bg-cream/10 hover:bg-success px-4 py-2 rounded-full text-sm transition">
        <i class="fa-brands fa-whatsapp"></i> <?= e($wa['label']) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="rounded-3xl overflow-hidden border border-line shadow-soft min-h-[340px]" data-aos="fade-left">
      <?php if ($mapsEmbed): ?>
        <iframe src="<?= e($mapsEmbed) ?>" class="w-full h-full min-h-[340px]" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Lumiere Cookies"></iframe>
      <?php else: ?>
        <div class="w-full h-full grid place-items-center bg-muted text-body">Peta belum tersedia</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require SRC_PATH . '/includes/footer.php'; ?>
