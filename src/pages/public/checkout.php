<?php
/*
FILE: src/pages/public/checkout.php
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Checkout — form pemesan, metode pengiriman, simpan order + order_items, invoice
*/
$activeNav = 'catalog';
$pageTitle = 'Checkout';

$pdo      = db();
$detailed = cart_detailed();           // NAILA YODA - data keranjang dari DB
$shipping = (int) setting('shipping_cost', '5000');
$errors   = [];
$old      = ['name' => '', 'phone' => '', 'delivery_method' => 'pickup', 'address' => '', 'maps_link' => '', 'notes' => ''];

// NAILA YODA - Proses submit checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); // NAILA YODA - proteksi CSRF

    $old['name']            = trim((string) ($_POST['name'] ?? ''));
    $old['phone']           = trim((string) ($_POST['phone'] ?? ''));
    $old['delivery_method'] = in_array($_POST['delivery_method'] ?? '', ['pickup', 'delivery', 'other'], true)
        ? $_POST['delivery_method'] : 'pickup';
    $old['address']         = trim((string) ($_POST['address'] ?? ''));
    $old['maps_link']       = trim((string) ($_POST['maps_link'] ?? ''));
    $old['notes']           = trim((string) ($_POST['notes'] ?? ''));

    if ($old['name'] === '')  $errors[] = 'Nama pemesan wajib diisi.';
    if ($old['phone'] === '') $errors[] = 'Nomor WhatsApp/HP wajib diisi.';
    if ($old['delivery_method'] === 'delivery' && $old['address'] === '') {
        $errors[] = 'Alamat pengiriman wajib diisi untuk metode "Dikirim ke Alamat".';
    }
    // NAILA YODA - Validasi link Google Maps (khusus pesanan diantar)
    if ($old['maps_link'] !== '' && !preg_match('~^https?://~i', $old['maps_link'])) {
        $errors[] = 'Link Google Maps harus diawali http:// atau https://';
    }
    if (empty($detailed['items'])) $errors[] = 'Keranjang masih kosong.';

    if (!$errors) {
        $shippingCost = $old['delivery_method'] === 'delivery' ? $shipping : 0;
        $subtotal     = (int) $detailed['subtotal'];
        $total        = $subtotal + $shippingCost;
        $invoice      = 'INV-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        try {
            $pdo->beginTransaction();

            // NAILA YODA - Simpan header order
            $mapsLink = $old['delivery_method'] === 'delivery' ? ($old['maps_link'] ?: null) : null;
            $stmt = $pdo->prepare(
                "INSERT INTO orders
                 (invoice_code, customer_name, customer_phone, delivery_method, address, maps_link, notes, subtotal, shipping_cost, total, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            $stmt->execute([
                $invoice, $old['name'], $old['phone'], $old['delivery_method'],
                $old['address'] ?: null, $mapsLink, $old['notes'] ?: null,
                $subtotal, $shippingCost, $total,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            // NAILA YODA - Simpan detail order + kurangi stok
            $itemStmt  = $pdo->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, price, qty, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stockStmt = $pdo->prepare(
                "UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?"
            );
            foreach ($detailed['items'] as $it) {
                $itemStmt->execute([$orderId, $it['id'], $it['name'], $it['price'], $it['qty'], $it['subtotal']]);
                $stockStmt->execute([$it['qty'], $it['id']]);
            }

            $pdo->commit();

            // NAILA YODA - Kosongkan keranjang, simpan ringkasan untuk halaman sukses
            cart_save([]);
            $_SESSION['last_order_id'] = $orderId;
            redirect(url('order-success', ['inv' => $invoice]));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Gagal menyimpan pesanan. Silakan coba lagi.';
        }
    }
}

require SRC_PATH . '/includes/header.php';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="mb-8" data-aos="fade-up">
    <h1 class="font-display text-3xl sm:text-4xl text-heading">Checkout</h1>
    <p class="text-body mt-1">Lengkapi data pemesanan Anda.</p>
  </div>

  <?php if ($errors): ?>
    <div class="mb-6 rounded-2xl border border-danger/30 bg-danger/10 text-danger px-5 py-4">
      <p class="font-semibold mb-1"><i class="fa-solid fa-circle-exclamation mr-2"></i>Periksa kembali:</p>
      <ul class="list-disc list-inside text-sm space-y-0.5">
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (empty($detailed['items'])): ?>
    <div class="bg-card border border-line rounded-2xl p-12 text-center shadow-soft">
      <i class="fa-solid fa-bag-shopping text-5xl text-line mb-4"></i>
      <p class="font-display text-2xl text-heading">Keranjang masih kosong</p>
      <p class="text-body mt-2 mb-6">Tambahkan produk terlebih dahulu sebelum checkout.</p>
      <a href="<?= url('catalog') ?>" class="inline-block bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">Mulai Belanja</a>
    </div>
  <?php else: ?>
  <form method="post" action="<?= url('checkout') ?>" class="grid lg:grid-cols-3 gap-8" id="checkoutForm">
    <?= csrf_field() ?>
    <!-- Data pemesan -->
    <div class="lg:col-span-2 space-y-6">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft" data-aos="fade-up">
        <h2 class="font-display text-xl text-heading mb-5">Data Pemesan</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-heading mb-1.5">Nama Lengkap <span class="text-danger">*</span></label>
            <input name="name" value="<?= e($old['name']) ?>" required
                   class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Nama Anda">
          </div>
          <div>
            <label class="block text-sm font-medium text-heading mb-1.5">No. WhatsApp / HP <span class="text-danger">*</span></label>
            <input name="phone" value="<?= e($old['phone']) ?>" required inputmode="tel"
                   class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="08xxxxxxxxxx">
          </div>
        </div>
      </div>

      <!-- Metode pengiriman -->
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft" data-aos="fade-up">
        <h2 class="font-display text-xl text-heading mb-5">Metode Pengambilan</h2>
        <div class="grid sm:grid-cols-3 gap-3" id="deliveryOptions">
          <?php
          $methods = [
            'pickup'   => ['ambil sesuai jadwal', 'fa-hand-holding-heart', 'silahkan isi deskripsi'],
            'delivery' => ['Dikirim ke Alamat', 'fa-truck-fast', rupiah($shipping)],
            'other'    => ['Opsi Lainnya', 'fa-comments', 'silahkan isi deskripsi'],
          ];
          foreach ($methods as $val => [$label, $icon, $cost]):
            $checked = $old['delivery_method'] === $val; ?>
          <label class="delivery-opt cursor-pointer rounded-2xl border-2 <?= $checked ? 'border-primary bg-primary/5' : 'border-line' ?> p-4 text-center transition hover:border-primary/60">
            <input type="radio" name="delivery_method" value="<?= $val ?>" class="sr-only" <?= $checked ? 'checked' : '' ?>>
            <i class="fa-solid <?= $icon ?> text-2xl text-primary mb-2"></i>
            <p class="font-medium text-heading text-sm"><?= $label ?></p>
            <p class="text-xs text-body mt-0.5"><?= $cost ?></p>
          </label>
          <?php endforeach; ?>
        </div>

        <div id="addressWrap" class="mt-5 <?= $old['delivery_method'] === 'delivery' ? '' : 'hidden' ?>">
          <label class="block text-sm font-medium text-heading mb-1.5">Alamat Pengiriman</label>
          <textarea name="address" rows="3"
                    class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Alamat lengkap penerima"><?= e($old['address']) ?></textarea>

          <label class="block text-sm font-medium text-heading mb-1.5 mt-4">
            <i class="fa-solid fa-location-dot text-primary mr-1"></i>Link Google Maps <span class="text-xs font-normal text-body/60">(khusus diantar — bantu kurir menemukan lokasi)</span>
          </label>
          <input name="maps_link" type="url" value="<?= e($old['maps_link']) ?>" inputmode="url"
                 class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="https://maps.app.goo.gl/...">
          <p class="text-xs text-body/60 mt-1.5">Buka Google Maps → pin lokasi Anda → tombol <b>Bagikan</b> → salin tautannya ke sini.</p>
        </div>

        <div class="mt-5">
          <label class="block text-sm font-medium text-heading mb-1.5">Catatan (opsional)</label>
          <textarea name="notes" rows="2"
                    class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Contoh: tanpa kacang, ucapan custom, dll."><?= e($old['notes']) ?></textarea>
        </div>
      </div>
    </div>

    <!-- Ringkasan -->
    <aside data-aos="fade-left">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
        <h2 class="font-display text-xl text-heading mb-5">Ringkasan</h2>
        <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
          <?php foreach ($detailed['items'] as $it): ?>
          <div class="flex gap-3 items-center">
            <img src="<?= e($it['image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover bg-muted">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-heading truncate"><?= e($it['name']) ?></p>
              <p class="text-xs text-body"><?= $it['qty'] ?> × <?= rupiah($it['price']) ?></p>
            </div>
            <span class="text-sm font-semibold text-heading"><?= rupiah($it['subtotal']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="border-t border-line mt-4 pt-4 space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-body">Subtotal</span><span class="font-semibold text-heading"><?= rupiah($detailed['subtotal']) ?></span></div>
          <div class="flex justify-between"><span class="text-body">Ongkir</span><span id="shipDisplay" class="font-semibold text-heading" data-ship="<?= $shipping ?>"><?= $old['delivery_method'] === 'delivery' ? rupiah($shipping) : 'Rp 0' ?></span></div>
          <div class="border-t border-line pt-2 flex justify-between items-center">
            <span class="font-medium text-heading">Total</span>
            <span id="totalDisplay" class="font-display text-2xl text-primary" data-sub="<?= (int) $detailed['subtotal'] ?>"><?= rupiah($detailed['subtotal'] + ($old['delivery_method'] === 'delivery' ? $shipping : 0)) ?></span>
          </div>
        </div>
        <button type="submit" class="btn-primary mt-6 w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft hover:scale-[1.02]">
          <i class="fa-solid fa-circle-check mr-2"></i>Buat Pesanan
        </button>
        <p class="text-xs text-body/70 text-center mt-3">Pesanan diteruskan ke WhatsApp setelah dibuat.</p>
      </div>
    </aside>
  </form>
  <?php endif; ?>
</section>

<!-- NAILA YODA - Toggle alamat & hitung ongkir realtime -->
<script>
(function () {
  const opts = document.querySelectorAll('input[name="delivery_method"]');
  const addressWrap = document.getElementById('addressWrap');
  const shipDisplay = document.getElementById('shipDisplay');
  const totalDisplay = document.getElementById('totalDisplay');
  if (!opts.length) return;
  const ship = parseInt(shipDisplay?.getAttribute('data-ship') || '0', 10);
  const sub  = parseInt(totalDisplay?.getAttribute('data-sub') || '0', 10);
  const rupiah = (n) => 'Rp ' + (n || 0).toLocaleString('id-ID');

  function refresh() {
    const val = document.querySelector('input[name="delivery_method"]:checked')?.value;
    const isDelivery = val === 'delivery';
    addressWrap.classList.toggle('hidden', !isDelivery);
    const cost = isDelivery ? ship : 0;
    if (shipDisplay) shipDisplay.textContent = rupiah(cost);
    if (totalDisplay) totalDisplay.textContent = rupiah(sub + cost);
    document.querySelectorAll('.delivery-opt').forEach((el) => {
      const checked = el.querySelector('input').checked;
      el.classList.toggle('border-primary', checked);
      el.classList.toggle('bg-primary/5', checked);
      el.classList.toggle('border-line', !checked);
    });
  }
  opts.forEach((o) => o.addEventListener('change', refresh));
  refresh();
})();
</script>
<?php require SRC_PATH . '/includes/footer.php'; ?>
