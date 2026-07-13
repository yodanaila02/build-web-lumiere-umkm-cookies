</main>

<?php
/*
FILE: src/includes/footer.php
RESPONSIBLE MEMBER: HYUGA
FEATURE: Footer, lokasi, social, script global
*/
$storeName = setting('store_name', 'Lumiere Cookies');
$address   = setting('address', '');
$igUrl     = setting('instagram_url', '#');
$ig        = setting('instagram', '');
$waPrimary = whatsapp_primary();
?>
<!-- HYUGA - Footer -->
<footer class="mt-24 bg-heading text-cream/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid gap-10 md:grid-cols-4">
    <div class="md:col-span-2">
      <div class="flex items-center gap-2 mb-4">
        <span class="grid place-items-center w-11 h-11 rounded-full bg-cream overflow-hidden"><img src="<?= asset('images/logo.png') ?>" alt="Lumiere Cookies" class="w-full h-full object-contain"></span>
        <span class="font-display text-2xl text-cream">Lumiere Cookies</span>
      </div>
      <p class="max-w-md text-sm leading-relaxed text-cream/70">
        <?= e(setting('about', 'Kukis, hampers, dan dessert box yang dipanggang segar setiap hari di Surakarta.')) ?>
      </p>
      <div class="flex items-center gap-3 mt-5">
        <?php if ($ig): ?>
        <a href="<?= e($igUrl) ?>" target="_blank" rel="noopener" class="w-10 h-10 grid place-items-center rounded-full bg-cream/10 hover:bg-primary transition"><i class="fa-brands fa-instagram"></i></a>
        <?php endif; ?>
        <?php if ($waPrimary): ?>
        <a href="<?= e(wa_link($waPrimary, 'Halo Lumiere Cookies!')) ?>" target="_blank" rel="noopener" class="w-10 h-10 grid place-items-center rounded-full bg-cream/10 hover:bg-success transition"><i class="fa-brands fa-whatsapp"></i></a>
        <?php endif; ?>
      </div>
    </div>

    <div>
      <h4 class="font-display text-lg text-cream mb-4">Jelajahi</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= url('home') ?>" class="hover:text-cream transition">Beranda</a></li>
        <li><a href="<?= url('catalog') ?>" class="hover:text-cream transition">Katalog Produk</a></li>
        <li><a href="<?= url('articles') ?>" class="hover:text-cream transition">Artikel</a></li>
        <li><a href="<?= url('contact') ?>" class="hover:text-cream transition">Kontak</a></li>
        <li><a href="<?= url('login') ?>" class="hover:text-cream transition">Login Admin</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-display text-lg text-cream mb-4">Lokasi</h4>
      <p class="text-sm leading-relaxed text-cream/70"><i class="fa-solid fa-location-dot mr-2 text-accent"></i><?= e($address) ?></p>
      <?php if ($ig): ?><p class="text-sm mt-3 text-cream/70"><i class="fa-brands fa-instagram mr-2 text-accent"></i><?= e($ig) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="border-t border-cream/10">
    <div class="max-w-7xl mx-auto px-4 py-5 text-center text-xs text-cream/50">
      © <?= date('Y') ?> Lumiere Cookies · Tugas Pemrograman Web · Dibuat dengan PHP Native + MySQL
    </div>
  </div>
</footer>

<!-- HYUGA - Floating WhatsApp button -->
<?php if ($waPrimary): ?>
<a href="<?= e(wa_link($waPrimary, 'Halo Lumiere Cookies, saya ingin memesan.')) ?>" target="_blank" rel="noopener"
   class="fixed bottom-5 right-5 z-30 w-14 h-14 grid place-items-center rounded-full bg-success text-white text-2xl shadow-lift hover:scale-110 transition"
   aria-label="Chat WhatsApp">
  <i class="fa-brands fa-whatsapp"></i>
</a>
<?php endif; ?>

<!-- Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.1.4/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/cart.js') ?>"></script>
</body>
</html>
