<?php
/*
FILE: src/pages/public/articles.php
RESPONSIBLE MEMBER: HYUGA
FEATURE: Daftar artikel/blog — diambil dari database, pagination
*/
$activeNav = 'articles';
$pageTitle = 'Artikel';

$pdo     = db();
$page    = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 6;

$total = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE is_published = 1")->fetchColumn();
$pg    = paginate($total, $perPage, $page);

$stmt = $pdo->prepare(
    "SELECT * FROM articles WHERE is_published = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?"
);
$stmt->bindValue(1, $pg['perPage'], PDO::PARAM_INT);
$stmt->bindValue(2, $pg['offset'], PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

require SRC_PATH . '/includes/header.php';
?>
<!-- HYUGA - Header artikel -->
<section class="bg-muted/40 border-b border-line">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center" data-aos="fade-up">
    <span class="text-xs tracking-[0.3em] uppercase text-accent font-semibold">Blog Lumiere</span>
    <h1 class="font-display text-4xl sm:text-5xl text-heading mt-3">Cerita & Tips Manis</h1>
    <p class="text-body mt-3 max-w-xl mx-auto">Resep, tips penyimpanan, dan kisah di balik dapur Lumiere Cookies.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <?php if (!$articles): ?>
    <div class="bg-card border border-line rounded-2xl p-12 text-center shadow-soft">
      <i class="fa-solid fa-newspaper text-5xl text-line mb-4"></i>
      <p class="font-display text-2xl text-heading">Belum ada artikel</p>
      <p class="text-body mt-2">Nantikan konten terbaru dari kami.</p>
    </div>
  <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($articles as $i => $a): ?>
      <article class="product-card bg-card border border-line rounded-2xl overflow-hidden shadow-soft flex flex-col" data-aos="fade-up" data-aos-delay="<?= $i * 70 ?>">
        <a href="<?= url('article', ['id' => (int) $a['id']]) ?>" class="block aspect-[16/10] overflow-hidden bg-muted">
          <img src="<?= e(article_image($a['image'])) ?>" alt="<?= e($a['title']) ?>" loading="lazy" class="w-full h-full object-cover">
        </a>
        <div class="p-5 flex flex-col flex-1">
          <p class="text-xs text-accent mb-2"><i class="fa-regular fa-calendar mr-1"></i><?= date('d M Y', strtotime($a['created_at'])) ?></p>
          <h2 class="font-display text-xl text-heading leading-snug line-clamp-2">
            <a href="<?= url('article', ['id' => (int) $a['id']]) ?>" class="hover:text-hover transition"><?= e($a['title']) ?></a>
          </h2>
          <p class="text-sm text-body mt-2 line-clamp-3 flex-1"><?= e($a['excerpt']) ?></p>
          <a href="<?= url('article', ['id' => (int) $a['id']]) ?>" class="mt-4 inline-flex items-center text-sm text-primary font-medium hover:text-hover transition">
            Baca selengkapnya <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($pg['pages'] > 1): ?>
    <div class="flex justify-center gap-2 mt-10">
      <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
        <a href="<?= url('articles', ['p' => $i]) ?>"
           class="w-10 h-10 grid place-items-center rounded-full border transition <?= $i === $pg['current'] ? 'bg-primary text-cream border-primary' : 'border-line text-body hover:border-primary' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</section>
<?php require SRC_PATH . '/includes/footer.php'; ?>
