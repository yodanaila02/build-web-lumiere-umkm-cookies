<?php
/*
FILE: src/pages/admin/orders.php
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Daftar pesanan — filter status, ringkasan, link detail, export CSV
*/
$pageTitle = 'Kelola Pesanan';
$adminPage = 'admin/orders';

$pdo = db();

// NAILA YODA - Filter status
$statusFilter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending', 'processing', 'done', 'cancelled'];
if (!in_array($statusFilter, $allowed, true)) $statusFilter = 'all';

$where  = '';
$params = [];
if ($statusFilter !== 'all') {
    $where = 'WHERE status = ?';
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("SELECT * FROM orders $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// NAILA YODA - Hitung jumlah per status untuk tab
$counts = ['all' => 0, 'pending' => 0, 'processing' => 0, 'done' => 0, 'cancelled' => 0];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM orders GROUP BY status") as $r) {
    $counts[$r['status']] = (int) $r['c'];
    $counts['all'] += (int) $r['c'];
}

require SRC_PATH . '/includes/admin_header.php';

$tabs = ['all' => 'Semua', 'pending' => 'Menunggu', 'processing' => 'Diproses', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div class="flex flex-wrap gap-2">
    <?php foreach ($tabs as $key => $label):
      $is = $statusFilter === $key; ?>
      <a href="<?= url('admin/orders', $key === 'all' ? [] : ['status' => $key]) ?>"
         class="text-sm px-4 py-2 rounded-full border transition <?= $is ? 'bg-primary text-cream border-primary' : 'border-line text-body hover:border-primary' ?>">
        <?= $label ?> <span class="opacity-70">(<?= $counts[$key] ?>)</span>
      </a>
    <?php endforeach; ?>
  </div>
  <a href="<?= url('admin/export', $statusFilter === 'all' ? [] : ['status' => $statusFilter]) ?>"
     class="inline-flex items-center gap-2 text-sm bg-success/10 text-success border border-success/30 px-4 py-2 rounded-xl hover:bg-success/20 transition">
    <i class="fa-solid fa-file-csv"></i> Export CSV
  </a>
</div>

<div class="bg-card border border-line rounded-2xl shadow-soft overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-muted/50 text-body text-left">
        <tr>
          <th class="px-5 py-3 font-medium">Invoice</th>
          <th class="px-5 py-3 font-medium">Pelanggan</th>
          <th class="px-5 py-3 font-medium">Metode</th>
          <th class="px-5 py-3 font-medium">Total</th>
          <th class="px-5 py-3 font-medium">Status</th>
          <th class="px-5 py-3 font-medium">Tanggal</th>
          <th class="px-5 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        <?php if (!$orders): ?>
          <tr><td colspan="7" class="px-5 py-10 text-center text-body">Tidak ada pesanan pada filter ini.</td></tr>
        <?php else: foreach ($orders as $o):
          [$stLabel, $stClass] = order_status_meta($o['status']);
          $method = ['pickup' => 'Ambil', 'delivery' => 'Kirim', 'other' => 'Lainnya'][$o['delivery_method']] ?? '-'; ?>
        <tr class="hover:bg-muted/30 transition">
          <td class="px-5 py-3 font-mono text-xs text-heading"><?= e($o['invoice_code']) ?></td>
          <td class="px-5 py-3">
            <p class="text-heading font-medium"><?= e($o['customer_name']) ?></p>
            <p class="text-xs text-body"><?= e($o['customer_phone']) ?></p>
          </td>
          <td class="px-5 py-3 text-body"><?= $method ?></td>
          <td class="px-5 py-3 font-medium text-heading"><?= rupiah($o['total']) ?></td>
          <td class="px-5 py-3"><span class="status-pill <?= $stClass ?>"><?= e($stLabel) ?></span></td>
          <td class="px-5 py-3 text-body"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
          <td class="px-5 py-3 text-right">
            <a href="<?= url('admin/order', ['id' => (int) $o['id']]) ?>" class="inline-flex items-center gap-1 text-primary hover:text-hover transition text-sm">
              Detail <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
