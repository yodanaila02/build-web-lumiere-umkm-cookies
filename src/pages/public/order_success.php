<?php
/*
FILE: src/pages/public/order_success.php
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Konfirmasi pesanan — invoice, detail, kirim format pesanan via WhatsApp
*/
$activeNav = '';
$pageTitle = 'Pesanan Berhasil';

$pdo     = db();
$invoice = trim((string) ($_GET['inv'] ?? ''));

// NAILA YODA - Ambil order berdasarkan invoice
$stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_code = ?");
$stmt->execute([$invoice]);
$order = $stmt->fetch();

if (!$order) {
    require SRC_PATH . '/includes/header.php';
    echo '<section class="max-w-2xl mx-auto px-4 py-24 text-center">
            <i class="fa-solid fa-receipt text-6xl text-line mb-6"></i>
            <h1 class="font-display text-3xl text-heading mb-3">Pesanan tidak ditemukan</h1>
            <a href="' . url('catalog') . '" class="inline-block mt-4 bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">Kembali Belanja</a>
          </section>';
    require SRC_PATH . '/includes/footer.php';
    return;
}

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

$methodLabel = ['pickup' => 'ambil sesuai jadwal', 'delivery' => 'Dikirim ke Alamat', 'other' => 'Opsi Lainnya'][$order['delivery_method']] ?? '-';

// NAILA YODA - Susun pesan WhatsApp dari isi order (format invoice)
$lines = [];
$lines[] = "Halo Lumiere Cookies! Saya ingin konfirmasi pesanan:";
$lines[] = "";
$lines[] = "Invoice: {$order['invoice_code']}";
$lines[] = "Nama: {$order['customer_name']}";
$lines[] = "HP: {$order['customer_phone']}";
$lines[] = "Pengambilan: {$methodLabel}";
if ($order['delivery_method'] === 'delivery' && $order['address']) {
    $lines[] = "Alamat: {$order['address']}";
}
if ($order['delivery_method'] === 'delivery' && !empty($order['maps_link'])) {
    $lines[] = "Lokasi (Maps): {$order['maps_link']}";
}
$lines[] = "";
$lines[] = "Pesanan:";
foreach ($items as $it) {
    $lines[] = "- {$it['product_name']} x{$it['qty']} = " . rupiah($it['subtotal']);
}
$lines[] = "";
$lines[] = "Subtotal: " . rupiah($order['subtotal']);
$lines[] = "Ongkir: " . rupiah($order['shipping_cost']);
$lines[] = "TOTAL: " . rupiah($order['total']);
if ($order['notes']) {
    $lines[] = "";
    $lines[] = "Catatan: {$order['notes']}";
}
$waText    = implode("\n", $lines);
$waPrimary = whatsapp_primary();
$waNumbers = whatsapp_numbers();

require SRC_PATH . '/includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <!-- NAILA YODA - Header sukses -->
  <div class="text-center mb-8" data-aos="zoom-in">
    <div class="w-20 h-20 mx-auto rounded-full bg-success/15 grid place-items-center mb-5">
      <i class="fa-solid fa-circle-check text-success text-4xl"></i>
    </div>
    <h1 class="font-display text-3xl sm:text-4xl text-heading">Pesanan Berhasil Dibuat!</h1>
    <p class="text-body mt-2">Simpan invoice ini & kirim konfirmasi via WhatsApp agar pesanan diproses.</p>
  </div>

  <!-- Invoice card -->
  <div class="bg-card border border-line rounded-3xl shadow-soft overflow-hidden" data-aos="fade-up">
    <div class="bg-heading text-cream px-6 py-5 flex items-center justify-between">
      <div>
        <p class="text-xs uppercase tracking-widest text-cream/60">Invoice</p>
        <p class="font-display text-xl"><?= e($order['invoice_code']) ?></p>
      </div>
      <div class="text-right">
        <p class="text-xs uppercase tracking-widest text-cream/60">Tanggal</p>
        <p class="text-sm"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
      </div>
    </div>

    <div class="px-6 py-5 grid sm:grid-cols-2 gap-4 border-b border-line text-sm">
      <div><span class="text-body">Nama</span><p class="font-medium text-heading"><?= e($order['customer_name']) ?></p></div>
      <div><span class="text-body">No. HP</span><p class="font-medium text-heading"><?= e($order['customer_phone']) ?></p></div>
      <div><span class="text-body">Metode</span><p class="font-medium text-heading"><?= e($methodLabel) ?></p></div>
      <?php if ($order['delivery_method'] === 'delivery' && $order['address']): ?>
      <div><span class="text-body">Alamat</span><p class="font-medium text-heading"><?= e($order['address']) ?></p></div>
      <?php endif; ?>
      <?php if ($order['delivery_method'] === 'delivery' && !empty($order['maps_link'])): ?>
      <div><span class="text-body">Lokasi Maps</span><p class="font-medium"><a href="<?= e($order['maps_link']) ?>" target="_blank" rel="noopener" class="text-primary hover:text-hover transition break-all"><i class="fa-solid fa-location-dot mr-1"></i>Buka di Google Maps</a></p></div>
      <?php endif; ?>
    </div>

    <div class="px-6 py-5 space-y-3">
      <?php foreach ($items as $it): ?>
      <div class="flex justify-between text-sm">
        <span class="text-heading"><?= e($it['product_name']) ?> <span class="text-body">× <?= (int) $it['qty'] ?></span></span>
        <span class="font-medium text-heading"><?= rupiah($it['subtotal']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="px-6 py-5 bg-muted/40 space-y-2 text-sm">
      <div class="flex justify-between"><span class="text-body">Subtotal</span><span class="text-heading"><?= rupiah($order['subtotal']) ?></span></div>
      <div class="flex justify-between"><span class="text-body">Ongkir</span><span class="text-heading"><?= rupiah($order['shipping_cost']) ?></span></div>
      <div class="flex justify-between items-center pt-2 border-t border-line">
        <span class="font-medium text-heading">Total</span>
        <span class="font-display text-2xl text-primary"><?= rupiah($order['total']) ?></span>
      </div>
    </div>
  </div>

  <!-- NAILA YODA - Tombol kirim WhatsApp -->
  <div class="mt-8 space-y-3" data-aos="fade-up">
    <?php if ($waPrimary): ?>
    <a href="<?= e(wa_link($waPrimary, $waText)) ?>" target="_blank" rel="noopener"
       class="flex items-center justify-center gap-2 w-full bg-success hover:bg-success/90 text-white font-semibold py-4 rounded-2xl transition shadow-soft hover:scale-[1.01]">
      <i class="fa-brands fa-whatsapp text-xl"></i> Kirim Konfirmasi via WhatsApp
    </a>
    <?php endif; ?>
    <?php if (count($waNumbers) > 1): ?>
      <div class="flex flex-wrap gap-2 justify-center">
        <?php foreach ($waNumbers as $wa): ?>
        <a href="<?= e(wa_link($wa['number'], $waText)) ?>" target="_blank" rel="noopener"
           class="text-sm text-success border border-success/30 hover:bg-success hover:text-white px-4 py-2 rounded-full transition">
          <i class="fa-brands fa-whatsapp mr-1"></i><?= e($wa['label']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <a href="<?= url('catalog') ?>" class="block text-center text-sm text-accent hover:text-hover transition pt-2">
      <i class="fa-solid fa-arrow-left mr-1"></i> Lanjut belanja
    </a>
  </div>
</section>
<?php require SRC_PATH . '/includes/footer.php'; ?>
