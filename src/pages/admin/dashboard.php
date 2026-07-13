<?php
/*
FILE: src/pages/admin/dashboard.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: Dashboard admin — ringkasan data, statistik revenue, Chart.js, pesanan terbaru
*/
$pageTitle = 'Dashboard';
$adminPage = 'admin';

$pdo = db();

// NAYLA - Statistik ringkas (dihitung dari database)
$totalProducts  = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalOrders    = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue   = (int) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status = 'done'")->fetchColumn();
$totalCustomers = (int) $pdo->query("SELECT COUNT(DISTINCT customer_phone) FROM orders")->fetchColumn();
$pendingOrders  = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$newMessages    = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

// NAYLA - Revenue 7 hari terakhir (untuk Chart.js)
$rows = $pdo->query(
    "SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS revenue, COUNT(*) AS cnt
     FROM orders
     WHERE created_at >= (CURDATE() - INTERVAL 6 DAY)
     GROUP BY DATE(created_at)"
)->fetchAll();
$byDate = [];
foreach ($rows as $r) { $byDate[$r['d']] = $r; }

$chartLabels = $chartRevenue = $chartCount = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i day"));
    $chartLabels[]  = date('d M', strtotime($day));
    $chartRevenue[] = (int) ($byDate[$day]['revenue'] ?? 0);
    $chartCount[]   = (int) ($byDate[$day]['cnt'] ?? 0);
}

// NAYLA - Distribusi status order (untuk doughnut)
$statusRows = $pdo->query("SELECT status, COUNT(*) c FROM orders GROUP BY status")->fetchAll();
$statusData = ['pending' => 0, 'processing' => 0, 'done' => 0, 'cancelled' => 0];
foreach ($statusRows as $s) { $statusData[$s['status']] = (int) $s['c']; }

// NAYLA - Pesanan terbaru
$recent = $pdo->query(
    "SELECT * FROM orders ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

require SRC_PATH . '/includes/admin_header.php';

$cards = [
    ['Total Produk', $totalProducts, 'fa-cookie-bite', 'text-primary bg-primary/10'],
    ['Total Pesanan', $totalOrders, 'fa-receipt', 'text-accent bg-accent/10'],
    ['Pendapatan (Selesai)', rupiah($totalRevenue), 'fa-sack-dollar', 'text-success bg-success/10'],
    ['Total Pelanggan', $totalCustomers, 'fa-users', 'text-warning bg-warning/10'],
];
?>
<!-- NAYLA - Kartu statistik -->
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
  <?php foreach ($cards as [$label, $value, $icon, $tone]): ?>
  <div class="bg-card border border-line rounded-2xl p-5 shadow-soft">
    <div class="flex items-center justify-between">
      <span class="w-11 h-11 grid place-items-center rounded-xl <?= $tone ?>"><i class="fa-solid <?= $icon ?>"></i></span>
    </div>
    <p class="text-2xl font-semibold text-heading mt-4"><?= is_int($value) ? $value : e($value) ?></p>
    <p class="text-sm text-body mt-1"><?= e($label) ?></p>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($pendingOrders || $newMessages): ?>
<div class="flex flex-wrap gap-3 mb-6">
  <?php if ($pendingOrders): ?>
  <a href="<?= url('admin/orders', ['status' => 'pending']) ?>" class="inline-flex items-center gap-2 text-sm bg-warning/10 text-warning border border-warning/30 px-4 py-2 rounded-full hover:bg-warning/20 transition">
    <i class="fa-solid fa-clock"></i> <?= $pendingOrders ?> pesanan menunggu diproses
  </a>
  <?php endif; ?>
  <?php if ($newMessages): ?>
  <a href="<?= url('admin/messages') ?>" class="inline-flex items-center gap-2 text-sm bg-primary/10 text-primary border border-primary/30 px-4 py-2 rounded-full hover:bg-primary/20 transition">
    <i class="fa-solid fa-envelope"></i> <?= $newMessages ?> pesan belum dibaca
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- NAYLA - Charts -->
<div class="grid lg:grid-cols-3 gap-6 mb-6">
  <div class="lg:col-span-2 bg-card border border-line rounded-2xl p-6 shadow-soft">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display text-lg text-heading">Pendapatan 7 Hari Terakhir</h2>
      <a href="<?= url('admin/orders') ?>" class="text-sm text-accent hover:text-hover transition">Detail</a>
    </div>
    <canvas id="revenueChart" height="120"></canvas>
  </div>
  <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
    <h2 class="font-display text-lg text-heading mb-4">Status Pesanan</h2>
    <canvas id="statusChart" height="160"></canvas>
  </div>
</div>

<!-- NAYLA - Pesanan terbaru -->
<div class="bg-card border border-line rounded-2xl shadow-soft overflow-hidden">
  <div class="px-6 py-4 border-b border-line flex items-center justify-between">
    <h2 class="font-display text-lg text-heading">Pesanan Terbaru</h2>
    <a href="<?= url('admin/orders') ?>" class="text-sm text-accent hover:text-hover transition">Lihat semua</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-muted/50 text-body text-left">
        <tr>
          <th class="px-6 py-3 font-medium">Invoice</th>
          <th class="px-6 py-3 font-medium">Pelanggan</th>
          <th class="px-6 py-3 font-medium">Total</th>
          <th class="px-6 py-3 font-medium">Status</th>
          <th class="px-6 py-3 font-medium">Tanggal</th>
          <th class="px-6 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-line">
        <?php if (!$recent): ?>
          <tr><td colspan="6" class="px-6 py-8 text-center text-body">Belum ada pesanan.</td></tr>
        <?php else: foreach ($recent as $o):
          [$stLabel, $stClass] = order_status_meta($o['status']); ?>
        <tr class="hover:bg-muted/30 transition">
          <td class="px-6 py-3 font-mono text-xs text-heading"><?= e($o['invoice_code']) ?></td>
          <td class="px-6 py-3 text-heading"><?= e($o['customer_name']) ?></td>
          <td class="px-6 py-3 font-medium text-heading"><?= rupiah($o['total']) ?></td>
          <td class="px-6 py-3"><span class="status-pill <?= $stClass ?>"><?= e($stLabel) ?></span></td>
          <td class="px-6 py-3 text-body"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td class="px-6 py-3 text-right">
            <a href="<?= url('admin/order', ['id' => (int) $o['id']]) ?>" class="text-primary hover:text-hover transition"><i class="fa-solid fa-eye"></i></a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- NAYLA - Inisialisasi Chart.js -->
<script>
const revenueData = {
  labels: <?= json_encode($chartLabels) ?>,
  revenue: <?= json_encode($chartRevenue) ?>,
  count: <?= json_encode($chartCount) ?>,
};
const statusData = <?= json_encode(array_values($statusData)) ?>;

const fmtRp = (v) => 'Rp ' + (v || 0).toLocaleString('id-ID');

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: revenueData.labels,
    datasets: [{
      label: 'Pendapatan',
      data: revenueData.revenue,
      borderColor: '#A47551',
      backgroundColor: 'rgba(164,117,81,0.12)',
      fill: true, tension: 0.4, borderWidth: 3,
      pointBackgroundColor: '#A47551', pointRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => fmtRp(c.parsed.y) } } },
    scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'Rp ' + (v/1000) + 'k' } } }
  }
});

new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: ['Menunggu', 'Diproses', 'Selesai', 'Dibatalkan'],
    datasets: [{
      data: statusData,
      backgroundColor: ['#E0A852', '#CB997E', '#6FA773', '#D9776A'],
      borderWidth: 0,
    }]
  },
  options: { responsive: true, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } } } }
});
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
