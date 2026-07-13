<?php
/*
FILE: src/pages/admin/categories.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: CRUD Kategori produk
*/
$pageTitle = 'Kelola Kategori';
$adminPage = 'admin/categories';

$pdo = db();

// NAYLA - Proses POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $delId = (int) ($_POST['id'] ?? 0);
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $cnt->execute([$delId]);
        if ((int) $cnt->fetchColumn() > 0) {
            flash('error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$delId]);
            flash('success', 'Kategori dihapus.');
        }
        redirect(url('admin/categories'));
    }

    $editId = (int) ($_POST['id'] ?? 0);
    $name   = trim((string) ($_POST['name'] ?? ''));
    $desc   = trim((string) ($_POST['description'] ?? ''));

    if ($name === '') {
        flash('error', 'Nama kategori wajib diisi.');
    } else {
        $slug = slugify($name);
        if ($editId > 0) {
            $pdo->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?")->execute([$name, $slug, $desc ?: null, $editId]);
            flash('success', 'Kategori diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)")->execute([$name, $slug, $desc ?: null]);
            flash('success', 'Kategori ditambahkan.');
        }
    }
    redirect(url('admin/categories'));
}

$editId = (int) ($_GET['id'] ?? 0);
$edit = null;
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $edit = $stmt->fetch() ?: null;
}

$categories = $pdo->query(
    "SELECT c.*, COUNT(p.id) AS total FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name"
)->fetchAll();

require SRC_PATH . '/includes/admin_header.php';
?>
<div class="grid lg:grid-cols-3 gap-6">
  <!-- Form -->
  <div class="lg:col-span-1">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
      <h2 class="font-display text-lg text-heading mb-4"><?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
      <form method="post" action="<?= url('admin/categories') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Nama Kategori</label>
          <input name="name" value="<?= e($edit['name'] ?? '') ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Deskripsi</label>
          <textarea name="description" rows="3" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= e($edit['description'] ?? '') ?></textarea>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn-primary flex-1 bg-primary hover:bg-hover text-cream font-semibold py-2.5 rounded-xl transition shadow-soft">
            <i class="fa-solid fa-floppy-disk mr-2"></i><?= $edit ? 'Simpan' : 'Tambah' ?>
          </button>
          <?php if ($edit): ?><a href="<?= url('admin/categories') ?>" class="px-4 py-2.5 rounded-xl border border-line text-body hover:bg-muted transition">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- List -->
  <div class="lg:col-span-2">
    <div class="bg-card border border-line rounded-2xl shadow-soft overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 text-body text-left">
          <tr><th class="px-5 py-3 font-medium">Nama</th><th class="px-5 py-3 font-medium">Slug</th><th class="px-5 py-3 font-medium">Produk</th><th class="px-5 py-3 text-right">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-line">
          <?php if (!$categories): ?>
            <tr><td colspan="4" class="px-5 py-10 text-center text-body">Belum ada kategori.</td></tr>
          <?php else: foreach ($categories as $c): ?>
          <tr class="hover:bg-muted/30 transition">
            <td class="px-5 py-3 font-medium text-heading"><?= e($c['name']) ?></td>
            <td class="px-5 py-3 text-body font-mono text-xs"><?= e($c['slug']) ?></td>
            <td class="px-5 py-3"><span class="badge badge-new"><?= (int) $c['total'] ?> produk</span></td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-2">
                <a href="<?= url('admin/categories', ['id' => (int) $c['id']]) ?>" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-primary/10 text-primary transition"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="<?= url('admin/categories') ?>" class="js-delete inline" data-name="<?= e($c['name']) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <button type="submit" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-danger/10 text-danger transition"><i class="fa-solid fa-trash-can"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('.js-delete').forEach((form) => {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    Swal.fire({ title: 'Hapus kategori?', text: form.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' })
      .then((r) => { if (r.isConfirmed) form.submit(); });
  });
});
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
