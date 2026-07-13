<?php
/*
FILE: src/pages/public/article.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: Detail artikel — konten lengkap + artikel lainnya
*/
$activeNav = 'articles';

$pdo = db();
$id  = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND is_published = 1");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Artikel tidak ditemukan';
    require SRC_PATH . '/includes/header.php';
    echo '<section class="max-w-2xl mx-auto px-4 py-24 text-center">
            <i class="fa-solid fa-newspaper text-6xl text-line mb-6"></i>
            <h1 class="font-display text-3xl text-heading mb-3">Artikel tidak ditemukan</h1>
            <a href="' . url('articles') . '" class="inline-block mt-4 bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition">Kembali ke Artikel</a>
          </section>';
    require SRC_PATH . '/includes/footer.php';
    return;
}

$pageTitle = $article['title'];

$others = $pdo->prepare("SELECT id, title, image, created_at FROM articles WHERE id <> ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3");
$others->execute([$id]);
$otherArticles = $others->fetchAll();

require SRC_PATH . '/includes/header.php';
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <nav class="text-sm text-body flex items-center gap-2 mb-6">
    <a href="<?= url('articles') ?>" class="hover:text-primary transition">Artikel</a>
    <i class="fa-solid fa-chevron-right text-[10px] text-line"></i>
    <span class="text-heading truncate"><?= e($article['title']) ?></span>
  </nav>

  <p class="text-sm text-accent mb-3"><i class="fa-regular fa-calendar mr-1"></i><?= date('d M Y', strtotime($article['created_at'])) ?></p>
  <h1 class="font-display text-3xl sm:text-4xl text-heading leading-tight" data-aos="fade-up"><?= e($article['title']) ?></h1>

  <div class="mt-7 rounded-3xl overflow-hidden border border-line shadow-soft aspect-[16/9] bg-muted" data-aos="fade-up">
    <img src="<?= e(article_image($article['image'])) ?>" alt="<?= e($article['title']) ?>" class="w-full h-full object-cover">
  </div>

  <div class="prose prose-stone max-w-none mt-8 text-body leading-relaxed text-[17px] whitespace-pre-line" data-aos="fade-up">
<?= e($article['content']) ?>
  </div>

  <div class="mt-10 pt-8 border-t border-line">
    <a href="<?= url('articles') ?>" class="inline-flex items-center text-primary font-medium hover:text-hover transition">
      <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke semua artikel
    </a>
  </div>
</article>

<?php if ($otherArticles): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
  <h2 class="font-display text-2xl text-heading mb-6">Artikel Lainnya</h2>
  <div class="grid md:grid-cols-3 gap-6">
    <?php foreach ($otherArticles as $i => $a): ?>
    <article class="product-card bg-card border border-line rounded-2xl overflow-hidden shadow-soft" data-aos="fade-up" data-aos-delay="<?= $i * 70 ?>">
      <a href="<?= url('article', ['id' => (int) $a['id']]) ?>" class="block aspect-[16/10] overflow-hidden bg-muted">
        <img src="<?= e(article_image($a['image'])) ?>" alt="<?= e($a['title']) ?>" loading="lazy" class="w-full h-full object-cover">
      </a>
      <div class="p-5">
        <p class="text-xs text-accent mb-1"><?= date('d M Y', strtotime($a['created_at'])) ?></p>
        <h3 class="font-display text-lg text-heading leading-snug line-clamp-2">
          <a href="<?= url('article', ['id' => (int) $a['id']]) ?>" class="hover:text-hover transition"><?= e($a['title']) ?></a>
        </h3>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php require SRC_PATH . '/includes/footer.php'; ?>
