<?php
/*
FILE: src/pages/public/catalog.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: Katalog — pencarian, filter kategori, sort harga/terbaru, pagination
*/
$activeNav = 'catalog';
$pageTitle = 'Katalog Produk';

$pdo = db();
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

// Input
$q       = trim((string) ($_GET['q'] ?? ''));
$catSlug = trim((string) ($_GET['category'] ?? ''));
$sort    = $_GET['sort'] ?? 'newest';
$page    = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 8;

// Bangun WHERE dinamis dengan prepared statement
$where  = ['p.is_active = 1'];
$params = [];

if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$activeCat = null;
if ($catSlug !== '') {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
    $stmt->execute([$catSlug]);
    $activeCat = $stmt->fetch() ?: null;
    if ($activeCat) {
        $where[] = 'p.category_id = ?';
        $params[] = $activeCat['id'];
    }
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$orderMap = [
    'newest'     => 'p.created_at DESC',
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name'       => 'p.name ASC',
];
$orderSql = $orderMap[$sort] ?? $orderMap['newest'];

// Total untuk pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pg = paginate($total, $perPage, $page);

// Query produk (JOIN ke categories)
$sql = "SELECT p.*, c.name AS category_name
        FROM products p JOIN categories c ON c.id = p.category_id
        $whereSql ORDER BY $orderSql LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Helper bangun URL filter mempertahankan parameter
function catalog_url(array $override = []): string
{
    $base = ['page' => 'catalog'];
    $cur = ['q' => $_GET['q'] ?? '', 'category' => $_GET['category'] ?? '', 'sort' => $_GET['sort'] ?? 'newest'];
    $merged = array_filter(array_merge($base, $cur, $override), fn($v) => $v !== '' && $v !== null);
    return APP_BASE . '/index.php?' . http_build_query($merged);
}

require SRC_PATH . '/includes/header.php';
?>

<section class="bg-muted/60 border-b border-line">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-display text-4xl sm:text-5xl text-heading" data-aos="fade-up">
      <?= $activeCat ? e($activeCat['name']) : 'Katalog Produk' ?>
    </h1>
    <p class="text-body mt-2" data-aos="fade-up" data-aos-delay="60">Temukan kukis, hampers, dan dessert box favoritmu.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <!-- NAYLA - Search & Filter bar -->
  <form method="get" action="<?= APP_BASE ?>/index.php" class="bg-card border border-line rounded-2xl shadow-soft p-4 flex flex-col md:flex-row gap-3 md:items-center mb-8">
    <input type="hidden" name="page" value="catalog">
    <div class="relative flex-1">
      <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-body/50"></i>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari produk…"
             class="field w-full bg-cream border border-line rounded-xl pl-11 pr-4 py-3 text-heading placeholder:text-body/50">
    </div>
    <select name="category" class="field bg-cream border border-line rounded-xl px-4 py-3 text-heading">
      <option value="">Semua Kategori</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= e($c['slug']) ?>" <?= $catSlug === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="sort" class="field bg-cream border border-line rounded-xl px-4 py-3 text-heading">
      <option value="newest"     <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
      <option value="price_low"  <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga Termurah</option>
      <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
      <option value="name"       <?= $sort === 'name' ? 'selected' : '' ?>>Nama A–Z</option>
    </select>
    <button type="submit" class="btn-primary bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">
      <i class="fa-solid fa-sliders mr-1"></i> Terapkan
    </button>
  </form>

  <!-- Chip kategori -->
  <div class="flex flex-wrap gap-2 mb-8">
    <a href="<?= catalog_url(['category' => '']) ?>" class="px-4 py-2 rounded-full text-sm border transition <?= $catSlug === '' ? 'bg-primary text-cream border-primary' : 'bg-card border-line text-body hover:border-primary' ?>">Semua</a>
    <?php foreach ($categories as $c): ?>
      <a href="<?= catalog_url(['category' => $c['slug']]) ?>" class="px-4 py-2 rounded-full text-sm border transition <?= $catSlug === $c['slug'] ? 'bg-primary text-cream border-primary' : 'bg-card border-line text-body hover:border-primary' ?>"><?= e($c['name']) ?></a>
    <?php endforeach; ?>
  </div>

  <p class="text-sm text-body mb-6"><?= $total ?> produk ditemukan<?= $q !== '' ? ' untuk "' . e($q) . '"' : '' ?></p>

  <?php if ($products): ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
      <?php foreach ($products as $i => $p) echo render_product_card($p, (string) (($i % 4) * 60)); ?>
    </div>

    <!-- Pagination -->
    <?php if ($pg['pages'] > 1): ?>
    <div class="flex items-center justify-center gap-2 mt-12">
      <?php if ($pg['current'] > 1): ?>
        <a href="<?= catalog_url(['p' => $pg['current'] - 1]) ?>" class="w-10 h-10 grid place-items-center rounded-full bg-card border border-line hover:border-primary transition"><i class="fa-solid fa-chevron-left text-sm"></i></a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
        <a href="<?= catalog_url(['p' => $i]) ?>" class="w-10 h-10 grid place-items-center rounded-full border transition <?= $i === $pg['current'] ? 'bg-primary text-cream border-primary' : 'bg-card border-line hover:border-primary' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($pg['current'] < $pg['pages']): ?>
        <a href="<?= catalog_url(['p' => $pg['current'] + 1]) ?>" class="w-10 h-10 grid place-items-center rounded-full bg-card border border-line hover:border-primary transition"><i class="fa-solid fa-chevron-right text-sm"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="bg-card border border-line rounded-2xl p-16 text-center shadow-soft">
      <i class="fa-solid fa-magnifying-glass text-5xl text-line mb-4"></i>
      <p class="font-display text-2xl text-heading">Produk tidak ditemukan</p>
      <p class="text-body mt-2">Coba kata kunci lain atau lihat semua produk.</p>
      <a href="<?= url('catalog') ?>" class="mt-6 inline-block bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">Reset Filter</a>
    </div>
  <?php endif; ?>
</section>

<?php require SRC_PATH . '/includes/footer.php'; ?>
