<?php
/*
FILE: src/pages/admin/articles.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: CRUD Artikel/blog + upload gambar + publish toggle
*/
$pageTitle = 'Kelola Artikel';
$adminPage = 'admin/articles';

$pdo    = db();
$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $delId = (int) ($_POST['id'] ?? 0);
        $s = $pdo->prepare("SELECT image FROM articles WHERE id = ?"); $s->execute([$delId]); $img = $s->fetchColumn();
        $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$delId]);
        if ($img && is_file(UPLOAD_ARTICLES . '/' . $img)) @unlink(UPLOAD_ARTICLES . '/' . $img);
        flash('success', 'Artikel dihapus.');
        redirect(url('admin/articles'));
    }

    $editId    = (int) ($_POST['id'] ?? 0);
    $title     = trim((string) ($_POST['title'] ?? ''));
    $excerpt   = trim((string) ($_POST['excerpt'] ?? ''));
    $content   = trim((string) ($_POST['content'] ?? ''));
    $published = isset($_POST['is_published']) ? 1 : 0;

    $errs = [];
    if ($title === '')   $errs[] = 'Judul wajib diisi.';
    if ($content === '') $errs[] = 'Konten wajib diisi.';

    $newImage = null;
    if (!$errs) { try { $newImage = handle_image_upload('image', UPLOAD_ARTICLES); } catch (RuntimeException $e) { $errs[] = $e->getMessage(); } }

    if (!$errs) {
        $slug = slugify($title);
        if ($excerpt === '') $excerpt = mb_substr(strip_tags($content), 0, 150);
        if ($editId > 0) {
            $s = $pdo->prepare("SELECT image FROM articles WHERE id = ?"); $s->execute([$editId]); $oldImg = $s->fetchColumn();
            $image = $newImage ?: $oldImg;
            $pdo->prepare("UPDATE articles SET title=?, slug=?, excerpt=?, content=?, image=?, is_published=? WHERE id=?")->execute([$title, $slug, $excerpt, $content, $image, $published, $editId]);
            if ($newImage && $oldImg && is_file(UPLOAD_ARTICLES . '/' . $oldImg)) @unlink(UPLOAD_ARTICLES . '/' . $oldImg);
            flash('success', 'Artikel diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO articles (title, slug, excerpt, content, image, is_published) VALUES (?, ?, ?, ?, ?, ?)")->execute([$title, $slug, $excerpt, $content, $newImage, $published]);
            flash('success', 'Artikel ditambahkan.');
        }
        redirect(url('admin/articles'));
    }
    $formError = $errs;
    $form = ['id'=>$editId,'title'=>$title,'excerpt'=>$excerpt,'content'=>$content,'is_published'=>$published,'image'=>null];
    $action = $editId > 0 ? 'edit' : 'new';
}

require SRC_PATH . '/includes/admin_header.php';

if ($action === 'new' || $action === 'edit'):
    if (!isset($form)) {
        if ($action === 'edit') {
            $s = $pdo->prepare("SELECT * FROM articles WHERE id = ?"); $s->execute([$id]); $a = $s->fetch();
            if (!$a) { echo '<p class="text-body">Artikel tidak ditemukan.</p>'; require SRC_PATH . '/includes/admin_footer.php'; return; }
            $form = ['id'=>$a['id'],'title'=>$a['title'],'excerpt'=>$a['excerpt'],'content'=>$a['content'],'is_published'=>$a['is_published'],'image'=>$a['image']];
        } else {
            $form = ['id'=>0,'title'=>'','excerpt'=>'','content'=>'','is_published'=>1,'image'=>null];
        }
    }
?>
  <a href="<?= url('admin/articles') ?>" class="inline-flex items-center text-sm text-accent hover:text-hover transition mb-5"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke daftar</a>
  <?php if (!empty($formError)): ?>
    <div class="mb-5 rounded-xl border border-danger/30 bg-danger/10 text-danger px-4 py-3 text-sm"><ul class="list-disc list-inside"><?php foreach ($formError as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" action="<?= url('admin/articles') ?>" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
    <div class="lg:col-span-2 space-y-5">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft space-y-4">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Judul</label>
          <input name="title" value="<?= e($form['title']) ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Ringkasan (opsional)</label>
          <textarea name="excerpt" rows="2" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Dibuat otomatis bila kosong"><?= e($form['excerpt']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Konten</label>
          <textarea name="content" rows="12" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= e($form['content']) ?></textarea>
        </div>
      </div>
    </div>
    <div class="space-y-5">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
        <label class="block text-sm font-medium text-heading mb-3">Gambar Sampul</label>
        <div class="aspect-[16/10] rounded-xl overflow-hidden bg-muted border border-line mb-3">
          <img id="imgPreview" src="<?= e(article_image($form['image'])) ?>" class="w-full h-full object-cover">
        </div>
        <input name="image" type="file" accept="image/jpeg,image/png,image/webp" id="imgInput" class="block w-full text-sm text-body file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-cream file:cursor-pointer hover:file:bg-hover">
        <p class="text-xs text-body/60 mt-2">JPG / PNG / WEBP, maks 3 MB.</p>
      </div>
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
        <label class="flex items-center gap-2 text-sm text-heading cursor-pointer">
          <input type="checkbox" name="is_published" value="1" <?= $form['is_published'] ? 'checked' : '' ?> class="rounded border-line text-primary focus:ring-primary"> Publikasikan artikel
        </label>
      </div>
      <button type="submit" class="btn-primary w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft"><i class="fa-solid fa-floppy-disk mr-2"></i>Simpan Artikel</button>
    </div>
  </form>
  <script>
  document.getElementById('imgInput')?.addEventListener('change', (e) => { const f = e.target.files[0]; if (!f) return; document.getElementById('imgPreview').src = URL.createObjectURL(f); });
  </script>
<?php else:
$articles = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC")->fetchAll();
?>
  <div class="flex items-center justify-between mb-5">
    <p class="text-sm text-body"><?= count($articles) ?> artikel</p>
    <a href="<?= url('admin/articles', ['action' => 'new']) ?>" class="btn-primary inline-flex items-center gap-2 bg-primary hover:bg-hover text-cream text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-soft"><i class="fa-solid fa-plus"></i> Tulis Artikel</a>
  </div>
  <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
    <?php if (!$articles): ?>
      <div class="md:col-span-2 xl:col-span-3 bg-card border border-line rounded-2xl p-10 text-center text-body shadow-soft">Belum ada artikel.</div>
    <?php else: foreach ($articles as $a): ?>
    <div class="bg-card border border-line rounded-2xl overflow-hidden shadow-soft">
      <div class="aspect-[16/10] bg-muted relative">
        <img src="<?= e(article_image($a['image'])) ?>" class="w-full h-full object-cover">
        <?php if (!$a['is_published']): ?><span class="status-pill status-cancelled absolute top-3 left-3">Draft</span><?php endif; ?>
      </div>
      <div class="p-4">
        <p class="text-xs text-accent mb-1"><?= date('d M Y', strtotime($a['created_at'])) ?></p>
        <h3 class="font-medium text-heading line-clamp-2"><?= e($a['title']) ?></h3>
        <div class="flex items-center gap-2 mt-3">
          <a href="<?= url('admin/articles', ['action' => 'edit', 'id' => (int) $a['id']]) ?>" class="flex-1 text-center text-sm border border-line rounded-lg py-1.5 hover:bg-muted transition text-primary"><i class="fa-solid fa-pen mr-1"></i>Edit</a>
          <form method="post" action="<?= url('admin/articles') ?>" class="js-delete" data-name="<?= e($a['title']) ?>">
            <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
            <button class="w-9 h-9 grid place-items-center rounded-lg hover:bg-danger/10 text-danger transition"><i class="fa-solid fa-trash-can"></i></button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <script>
  document.querySelectorAll('.js-delete').forEach((form) => { form.addEventListener('submit', (e) => { e.preventDefault();
    Swal.fire({ title: 'Hapus artikel?', text: form.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then((r) => { if (r.isConfirmed) form.submit(); });
  }); });
  </script>
<?php endif; ?>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
