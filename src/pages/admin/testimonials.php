<?php
/*
FILE: src/pages/admin/testimonials.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: CRUD Testimoni pelanggan + aktif/nonaktif
*/
$pageTitle = 'Kelola Testimoni';
$adminPage = 'admin/testimonials';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Testimoni dihapus.');
        redirect(url('admin/testimonials'));
    }
    if ($do === 'toggle') {
        $pdo->prepare("UPDATE testimonials SET is_active = 1 - is_active WHERE id = ?")->execute([(int) ($_POST['id'] ?? 0)]);
        redirect(url('admin/testimonials'));
    }

    $editId = (int) ($_POST['id'] ?? 0);
    $name   = trim((string) ($_POST['customer_name'] ?? ''));
    $content= trim((string) ($_POST['content'] ?? ''));
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '' || $content === '') {
        flash('error', 'Nama dan isi testimoni wajib diisi.');
    } else {
        if ($editId > 0) {
            $pdo->prepare("UPDATE testimonials SET customer_name=?, content=?, rating=?, is_active=? WHERE id=?")->execute([$name, $content, $rating, $active, $editId]);
            flash('success', 'Testimoni diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO testimonials (customer_name, content, rating, is_active) VALUES (?, ?, ?, ?)")->execute([$name, $content, $rating, $active]);
            flash('success', 'Testimoni ditambahkan.');
        }
    }
    redirect(url('admin/testimonials'));
}

$editId = (int) ($_GET['id'] ?? 0);
$edit = null;
if ($editId > 0) { $s = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?"); $s->execute([$editId]); $edit = $s->fetch() ?: null; }
$items = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();

require SRC_PATH . '/includes/admin_header.php';
?>
<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
      <h2 class="font-display text-lg text-heading mb-4"><?= $edit ? 'Edit Testimoni' : 'Tambah Testimoni' ?></h2>
      <form method="post" action="<?= url('admin/testimonials') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Nama Pelanggan</label>
          <input name="customer_name" value="<?= e($edit['customer_name'] ?? '') ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Isi Testimoni</label>
          <textarea name="content" rows="4" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= e($edit['content'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Rating</label>
          <select name="rating" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            <?php for ($r = 5; $r >= 1; $r--): ?>
              <option value="<?= $r ?>" <?= (int) ($edit['rating'] ?? 5) === $r ? 'selected' : '' ?>><?= str_repeat('★', $r) . str_repeat('☆', 5 - $r) ?> (<?= $r ?>)</option>
            <?php endfor; ?>
          </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-heading cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?> class="rounded border-line text-primary focus:ring-primary"> Tampilkan di situs
        </label>
        <div class="flex gap-2">
          <button type="submit" class="btn-primary flex-1 bg-primary hover:bg-hover text-cream font-semibold py-2.5 rounded-xl transition shadow-soft"><i class="fa-solid fa-floppy-disk mr-2"></i><?= $edit ? 'Simpan' : 'Tambah' ?></button>
          <?php if ($edit): ?><a href="<?= url('admin/testimonials') ?>" class="px-4 py-2.5 rounded-xl border border-line text-body hover:bg-muted transition">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="lg:col-span-2 space-y-3">
    <?php if (!$items): ?>
      <div class="bg-card border border-line rounded-2xl p-10 text-center text-body shadow-soft">Belum ada testimoni.</div>
    <?php else: foreach ($items as $t): ?>
    <div class="bg-card border border-line rounded-2xl p-5 shadow-soft">
      <div class="flex items-start justify-between gap-3">
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <p class="font-medium text-heading"><?= e($t['customer_name']) ?></p>
            <span class="text-warning text-sm"><?= str_repeat('★', (int) $t['rating']) ?></span>
            <?php if (!$t['is_active']): ?><span class="status-pill status-cancelled">Nonaktif</span><?php endif; ?>
          </div>
          <p class="text-sm text-body mt-1.5">"<?= e($t['content']) ?>"</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
          <form method="post" action="<?= url('admin/testimonials') ?>" class="inline">
            <?= csrf_field() ?><input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
            <button class="w-8 h-8 grid place-items-center rounded-lg hover:bg-muted text-body transition" title="Aktif/Nonaktif"><i class="fa-solid <?= $t['is_active'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i></button>
          </form>
          <a href="<?= url('admin/testimonials', ['id' => (int) $t['id']]) ?>" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-primary/10 text-primary transition"><i class="fa-solid fa-pen"></i></a>
          <form method="post" action="<?= url('admin/testimonials') ?>" class="js-delete inline" data-name="<?= e($t['customer_name']) ?>">
            <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
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
    Swal.fire({ title: 'Hapus testimoni?', text: form.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then((r) => { if (r.isConfirmed) form.submit(); });
  });
});
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
