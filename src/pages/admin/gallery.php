<?php
/*
FILE: src/pages/admin/gallery.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: Kelola galeri — upload gambar, judul, hapus
*/
$pageTitle = 'Kelola Galeri';
$adminPage = 'admin/gallery';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $delId = (int) ($_POST['id'] ?? 0);
        $s = $pdo->prepare("SELECT image FROM gallery WHERE id = ?"); $s->execute([$delId]); $img = $s->fetchColumn();
        $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$delId]);
        if ($img && is_file(UPLOAD_GALLERY . '/' . $img)) @unlink(UPLOAD_GALLERY . '/' . $img);
        flash('success', 'Foto dihapus dari galeri.');
        redirect(url('admin/gallery'));
    }

    // Upload baru
    $title = trim((string) ($_POST['title'] ?? ''));
    try {
        $image = handle_image_upload('image', UPLOAD_GALLERY);
        if (!$image) {
            flash('error', 'Silakan pilih file gambar terlebih dahulu.');
        } else {
            $pdo->prepare("INSERT INTO gallery (title, image) VALUES (?, ?)")->execute([$title ?: null, $image]);
            flash('success', 'Foto ditambahkan ke galeri.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect(url('admin/gallery'));
}

$items = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC")->fetchAll();

require SRC_PATH . '/includes/admin_header.php';
?>
<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
      <h2 class="font-display text-lg text-heading mb-4">Tambah Foto</h2>
      <form method="post" action="<?= url('admin/gallery') ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Judul (opsional)</label>
          <input name="title" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Contoh: Proses memanggang">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Gambar</label>
          <div class="aspect-square rounded-xl overflow-hidden bg-muted border border-line mb-3">
            <img id="imgPreview" src="<?= e(asset('images/placeholder.svg')) ?>" class="w-full h-full object-cover">
          </div>
          <input name="image" type="file" accept="image/jpeg,image/png,image/webp" id="imgInput" required class="block w-full text-sm text-body file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-cream file:cursor-pointer hover:file:bg-hover">
          <p class="text-xs text-body/60 mt-2">JPG / PNG / WEBP, maks 3 MB.</p>
        </div>
        <button type="submit" class="btn-primary w-full bg-primary hover:bg-hover text-cream font-semibold py-2.5 rounded-xl transition shadow-soft"><i class="fa-solid fa-upload mr-2"></i>Unggah</button>
      </form>
    </div>
  </div>

  <div class="lg:col-span-2">
    <?php if (!$items): ?>
      <div class="bg-card border border-line rounded-2xl p-10 text-center text-body shadow-soft">Galeri masih kosong.</div>
    <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <?php foreach ($items as $g): ?>
      <div class="group relative aspect-square rounded-2xl overflow-hidden bg-muted border border-line shadow-soft">
        <img src="<?= e(gallery_image($g['image'])) ?>" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-heading/0 group-hover:bg-heading/50 transition flex items-end p-3 opacity-0 group-hover:opacity-100">
          <p class="text-cream text-xs flex-1 truncate"><?= e($g['title'] ?? '') ?></p>
          <form method="post" action="<?= url('admin/gallery') ?>" class="js-delete">
            <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
            <button class="w-8 h-8 grid place-items-center rounded-lg bg-cream/90 text-danger hover:bg-cream transition"><i class="fa-solid fa-trash-can"></i></button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<script>
document.getElementById('imgInput')?.addEventListener('change', (e) => { const f = e.target.files[0]; if (!f) return; document.getElementById('imgPreview').src = URL.createObjectURL(f); });
document.querySelectorAll('.js-delete').forEach((form) => { form.addEventListener('submit', (e) => { e.preventDefault();
  Swal.fire({ title: 'Hapus foto ini?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then((r) => { if (r.isConfirmed) form.submit(); });
}); });
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
