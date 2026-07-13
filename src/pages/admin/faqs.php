<?php
/*
FILE: src/pages/admin/faqs.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: CRUD FAQ + urutan tampil + aktif/nonaktif
*/
$pageTitle = 'Kelola FAQ';
$adminPage = 'admin/faqs';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'FAQ dihapus.');
        redirect(url('admin/faqs'));
    }

    $editId   = (int) ($_POST['id'] ?? 0);
    $question = trim((string) ($_POST['question'] ?? ''));
    $answer   = trim((string) ($_POST['answer'] ?? ''));
    $sort     = (int) ($_POST['sort_order'] ?? 0);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    if ($question === '' || $answer === '') {
        flash('error', 'Pertanyaan dan jawaban wajib diisi.');
    } else {
        if ($editId > 0) {
            $pdo->prepare("UPDATE faqs SET question=?, answer=?, sort_order=?, is_active=? WHERE id=?")->execute([$question, $answer, $sort, $active, $editId]);
            flash('success', 'FAQ diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?, ?, ?, ?)")->execute([$question, $answer, $sort, $active]);
            flash('success', 'FAQ ditambahkan.');
        }
    }
    redirect(url('admin/faqs'));
}

$editId = (int) ($_GET['id'] ?? 0);
$edit = null;
if ($editId > 0) { $s = $pdo->prepare("SELECT * FROM faqs WHERE id = ?"); $s->execute([$editId]); $edit = $s->fetch() ?: null; }
$items = $pdo->query("SELECT * FROM faqs ORDER BY sort_order, id")->fetchAll();

require SRC_PATH . '/includes/admin_header.php';
?>
<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
      <h2 class="font-display text-lg text-heading mb-4"><?= $edit ? 'Edit FAQ' : 'Tambah FAQ' ?></h2>
      <form method="post" action="<?= url('admin/faqs') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Pertanyaan</label>
          <input name="question" value="<?= e($edit['question'] ?? '') ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Jawaban</label>
          <textarea name="answer" rows="4" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= e($edit['answer'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Urutan</label>
          <input name="sort_order" type="number" value="<?= (int) ($edit['sort_order'] ?? 0) ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <label class="flex items-center gap-2 text-sm text-heading cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?> class="rounded border-line text-primary focus:ring-primary"> Tampilkan di situs
        </label>
        <div class="flex gap-2">
          <button type="submit" class="btn-primary flex-1 bg-primary hover:bg-hover text-cream font-semibold py-2.5 rounded-xl transition shadow-soft"><i class="fa-solid fa-floppy-disk mr-2"></i><?= $edit ? 'Simpan' : 'Tambah' ?></button>
          <?php if ($edit): ?><a href="<?= url('admin/faqs') ?>" class="px-4 py-2.5 rounded-xl border border-line text-body hover:bg-muted transition">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="lg:col-span-2 space-y-3">
    <?php if (!$items): ?>
      <div class="bg-card border border-line rounded-2xl p-10 text-center text-body shadow-soft">Belum ada FAQ.</div>
    <?php else: foreach ($items as $f): ?>
    <div class="bg-card border border-line rounded-2xl p-5 shadow-soft">
      <div class="flex items-start justify-between gap-3">
        <div class="flex-1">
          <p class="font-medium text-heading flex items-center gap-2">
            <span class="text-xs text-body bg-muted px-2 py-0.5 rounded">#<?= (int) $f['sort_order'] ?></span>
            <?= e($f['question']) ?>
            <?php if (!$f['is_active']): ?><span class="status-pill status-cancelled">Nonaktif</span><?php endif; ?>
          </p>
          <p class="text-sm text-body mt-1.5"><?= e($f['answer']) ?></p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
          <a href="<?= url('admin/faqs', ['id' => (int) $f['id']]) ?>" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-primary/10 text-primary transition"><i class="fa-solid fa-pen"></i></a>
          <form method="post" action="<?= url('admin/faqs') ?>" class="js-delete inline" data-name="<?= e($f['question']) ?>">
            <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
            <button class="w-8 h-8 grid place-items-center rounded-lg hover:bg-danger/10 text-danger transition"><i class="fa-solid fa-trash-can"></i></button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<script>
document.querySelectorAll('.js-delete').forEach((form) => {
  form.addEventListener('submit', (e) => { e.preventDefault();
    Swal.fire({ title: 'Hapus FAQ?', text: form.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then((r) => { if (r.isConfirmed) form.submit(); });
  });
});
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
