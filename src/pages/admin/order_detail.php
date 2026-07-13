<?php
/*
FILE: src/pages/admin/order_detail.php
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Detail pesanan — daftar item, ubah status, hubungi pelanggan via WhatsApp
*/
$pageTitle = 'Detail Pesanan';
$adminPage = 'admin/orders';

$pdo = db();
$id  = (int) ($_GET['id'] ?? 0);

// NAILA YODA - Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending', 'processing', 'done', 'cancelled'], true)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        flash('success', 'Status pesanan diperbarui.');
    }
    redirect(url('admin/order', ['id' => $id]));
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

require SRC_PATH . '/includes/admin_header.php';

if (!$order) {
    echo '<p class="text-body">Pesanan tidak ditemukan. <a href="' . url('admin/orders') . '" class="text-primary">Kembali</a></p>';
    require SRC_PATH . '/includes/admin_footer.php';
    return;
}

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();

[$stLabel, $stClass] = order_status_meta($order['status']);
$method = ['pickup' => 'ambil sesuai jadwal', 'delivery' => 'Dikirim ke Alamat', 'other' => 'Opsi Lainnya'][$order['delivery_method']] ?? '-';
$waText = "Halo {$order['customer_name']}, pesanan Anda ({$order['invoice_code']}) di Lumiere Cookies ";
?>
<a href="<?= url('admin/orders') ?>" class="inline-flex items-center text-sm text-accent hover:text-hover transition mb-5"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke daftar pesanan</a>

<div class="grid lg:grid-cols-3 gap-6">
  <!-- Detail item -->
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-card border border-line rounded-2xl shadow-soft overflow-hidden">
      <div class="px-6 py-5 border-b border-line flex items-center justify-between">
        <div>
          <p class="font-display text-xl text-heading"><?= e($order['invoice_code']) ?></p>
          <p class="text-sm text-body"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB</p>
        </div>
        <span class="status-pill <?= $stClass ?>"><?= e($stLabel) ?></span>
      </div>
      <div class="px-6 py-4">
        <table class="w-full text-sm">
          <thead class="text-body text-left border-b border-line">
            <tr><th class="py-2 font-medium">Produk</th><th class="py-2 font-medium text-center">Qty</th><th class="py-2 font-medium text-right">Harga</th><th class="py-2 font-medium text-right">Subtotal</th></tr>
          </thead>
          <tbody class="divide-y divide-line">
            <?php foreach ($items as $it): ?>
            <tr>
              <td class="py-3 text-heading"><?= e($it['product_name']) ?></td>
              <td class="py-3 text-center text-body"><?= (int) $it['qty'] ?></td>
              <td class="py-3 text-right text-body"><?= rupiah($it['price']) ?></td>
              <td class="py-3 text-right font-medium text-heading"><?= rupiah($it['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="px-6 py-4 bg-muted/40 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-body">Subtotal</span><span class="text-heading"><?= rupiah($order['subtotal']) ?></span></div>
        <div class="flex justify-between"><span class="text-body">Ongkir</span><span class="text-heading"><?= rupiah($order['shipping_cost']) ?></span></div>
        <div class="flex justify-between items-center pt-2 border-t border-line"><span class="font-medium text-heading">Total</span><span class="font-display text-2xl text-primary"><?= rupiah($order['total']) ?></span></div>
      </div>
    </div>

    <?php if ($order['notes']): ?>
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
      <h3 class="font-medium text-heading mb-2"><i class="fa-solid fa-note-sticky text-accent mr-2"></i>Catatan Pelanggan</h3>
      <p class="text-body text-sm"><?= e($order['notes']) ?></p>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar aksi -->
  <div class="space-y-6">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
      <h3 class="font-display text-lg text-heading mb-4">Pelanggan</h3>
      <ul class="space-y-3 text-sm">
        <li><span class="text-body block text-xs">Nama</span><span class="text-heading font-medium"><?= e($order['customer_name']) ?></span></li>
        <li><span class="text-body block text-xs">No. HP</span><span class="text-heading font-medium"><?= e($order['customer_phone']) ?></span></li>
        <li><span class="text-body block text-xs">Metode</span><span class="text-heading font-medium"><?= e($method) ?></span></li>
        <?php if ($order['delivery_method'] === 'delivery' && $order['address']): ?>
        <li><span class="text-body block text-xs">Alamat</span><span class="text-heading"><?= e($order['address']) ?></span></li>
        <?php endif; ?>
        <?php if ($order['delivery_method'] === 'delivery' && !empty($order['maps_link'])): ?>
        <li><span class="text-body block text-xs">Lokasi Maps</span><a href="<?= e($order['maps_link']) ?>" target="_blank" rel="noopener" class="text-primary hover:text-hover transition break-all text-sm"><i class="fa-solid fa-location-dot mr-1"></i>Buka di Google Maps</a></li>
        <?php endif; ?>
      </ul>
      <a href="<?= e(wa_link($order['customer_phone'], $waText)) ?>" target="_blank" rel="noopener"
         class="mt-4 flex items-center justify-center gap-2 w-full bg-success/10 text-success border border-success/30 hover:bg-success hover:text-white font-medium py-2.5 rounded-xl transition">
        <i class="fa-brands fa-whatsapp"></i> Hubungi via WhatsApp
      </a>
    </div>

    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
      <h3 class="font-display text-lg text-heading mb-4">Ubah Status</h3>
      <form method="post" action="<?= url('admin/order', ['id' => $id]) ?>" class="space-y-3">
        <?= csrf_field() ?>
        <?php
        $statuses = ['pending' => 'Menunggu', 'processing' => 'Diproses', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];
        foreach ($statuses as $val => $label):
          $checked = $order['status'] === $val; ?>
        <label class="flex items-center gap-3 px-4 py-2.5 rounded-xl border cursor-pointer transition <?= $checked ? 'border-primary bg-primary/5' : 'border-line hover:border-primary/50' ?>">
          <input type="radio" name="status" value="<?= $val ?>" <?= $checked ? 'checked' : '' ?> class="text-primary focus:ring-primary">
          <span class="text-sm text-heading"><?= $label ?></span>
        </label>
        <?php endforeach; ?>
        <button type="submit" class="btn-primary w-full bg-primary hover:bg-hover text-cream font-semibold py-2.5 rounded-xl transition shadow-soft mt-2">
          <i class="fa-solid fa-check mr-2"></i>Perbarui Status
        </button>
      </form>
    </div>
  </div>
</div>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
