<?php
/*
FILE: src/pages/admin/products.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: CRUD Produk — create, read, update, delete, upload gambar, badge, stok
*/
$pageTitle = 'Kelola Produk';
$adminPage = 'admin/products';

$pdo    = db();
$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);

// NAYLA - Proses simpan / hapus (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); // NAYLA - CSRF
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $delId = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$delId]);
        $img = $stmt->fetchColumn();
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
        if ($img && is_file(UPLOAD_PRODUCTS . '/' . $img)) @unlink(UPLOAD_PRODUCTS . '/' . $img);
        flash('success', 'Produk berhasil dihapus.');
        redirect(url('admin/products'));
    }

    // Create / Update
    $editId      = (int) ($_POST['id'] ?? 0);
    $name        = trim((string) ($_POST['name'] ?? ''));
    $categoryId  = (int) ($_POST['category_id'] ?? 0);
    $price       = (int) ($_POST['price'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);
    $description = trim((string) ($_POST['description'] ?? ''));
    $badge       = in_array($_POST['badge'] ?? 'none', ['none','best_seller','new','limited','pre_order'], true) ? $_POST['badge'] : 'none';
    $isActive    = isset($_POST['is_active']) ? 1 : 0;
    $isBest      = $badge === 'best_seller' ? 1 : (isset($_POST['is_best_seller']) ? 1 : 0);

    $errs = [];
    if ($name === '')       $errs[] = 'Nama produk wajib diisi.';
    if ($categoryId <= 0)   $errs[] = 'Kategori wajib dipilih.';
    if ($price < 0)         $errs[] = 'Harga tidak valid.';

    $newImage = null;
    if (!$errs) {
        try { $newImage = handle_image_upload('image', UPLOAD_PRODUCTS); }
        catch (RuntimeException $e) { $errs[] = $e->getMessage(); }
    }

    if (!$errs) {
        $slug = slugify($name);
        if ($editId > 0) {
            // Update
            $old = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $old->execute([$editId]);
            $oldImg = $old->fetchColumn();
            $image = $newImage ?: $oldImg;
            $pdo->prepare(
                "UPDATE products SET category_id=?, name=?, slug=?, description=?, price=?, stock=?, image=?, badge=?, is_best_seller=?, is_active=? WHERE id=?"
            )->execute([$categoryId, $name, $slug, $description ?: null, $price, $stock, $image, $badge, $isBest, $isActive, $editId]);
            if ($newImage && $oldImg && is_file(UPLOAD_PRODUCTS . '/' . $oldImg)) @unlink(UPLOAD_PRODUCTS . '/' . $oldImg);
            flash('success', 'Produk berhasil diperbarui.');
        } else {
            // Create
            $pdo->prepare(
                "INSERT INTO products (category_id, name, slug, description, price, stock, image, badge, is_best_seller, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            )->execute([$categoryId, $name, $slug, $description ?: null, $price, $stock, $newImage, $badge, $isBest, $isActive]);
            flash('success', 'Produk baru berhasil ditambahkan.');
        }
        redirect(url('admin/products'));
    }

    // Bila error, pertahankan input
    $formError = $errs;
    $form = compact('name','categoryId','price','stock','description','badge','isActive','isBest') + ['id' => $editId, 'image' => null];
    $action = $editId > 0 ? 'edit' : 'new';
    $id = $editId;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require SRC_PATH . '/includes/admin_header.php';

// ============ FORM (new / edit) ============
if ($action === 'new' || $action === 'edit'):
    if (!isset($form)) {
        if ($action === 'edit') {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $p = $stmt->fetch();
            if (!$p) { echo '<p class="text-body">Produk tidak ditemukan.</p>'; require SRC_PATH . '/includes/admin_footer.php'; return; }
            $form = ['id'=>$p['id'],'name'=>$p['name'],'categoryId'=>$p['category_id'],'price'=>$p['price'],'stock'=>$p['stock'],'description'=>$p['description'],'badge'=>$p['badge'],'isActive'=>$p['is_active'],'isBest'=>$p['is_best_seller'],'image'=>$p['image']];
        } else {
            $form = ['id'=>0,'name'=>'','categoryId'=>0,'price'=>0,'stock'=>0,'description'=>'','badge'=>'none','isActive'=>1,'isBest'=>0,'image'=>null];
        }
    }
    $badges = ['none'=>'Tanpa Badge','best_seller'=>'Best Seller','new'=>'New','limited'=>'Limited','pre_order'=>'Pre-Order'];
?>
  <a href="<?= url('admin/products') ?>" class="inline-flex items-center text-sm text-accent hover:text-hover transition mb-5"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke daftar</a>

  <?php if (!empty($formError)): ?>
    <div class="mb-5 rounded-xl border border-danger/30 bg-danger/10 text-danger px-4 py-3 text-sm">
      <ul class="list-disc list-inside space-y-0.5"><?php foreach ($formError as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= url('admin/products') ?>" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
    <div class="lg:col-span-2 space-y-5">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft space-y-4">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Nama Produk <span class="text-danger">*</span></label>
          <input name="name" value="<?= e($form['name']) ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Deskripsi</label>
          <textarea name="description" rows="5" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= e($form['description']) ?></textarea>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-heading mb-1.5">Harga (Rp) <span class="text-danger">*</span></label>
            <input name="price" type="number" min="0" value="<?= (int) $form['price'] ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
          </div>
          <div>
            <label class="block text-sm font-medium text-heading mb-1.5">Stok</label>
            <input name="stock" type="number" min="0" value="<?= (int) $form['stock'] ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-5">
      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft space-y-4">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Kategori <span class="text-danger">*</span></label>
          <select name="category_id" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            <option value="">— Pilih —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= (int) $form['categoryId'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Badge</label>
          <select name="badge" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            <?php foreach ($badges as $val => $label): ?>
              <option value="<?= $val ?>" <?= $form['badge'] === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-heading cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= $form['isActive'] ? 'checked' : '' ?> class="rounded border-line text-primary focus:ring-primary"> Produk aktif (tampil di situs)
        </label>
      </div>

      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
        <label class="block text-sm font-medium text-heading mb-3">Gambar Produk</label>
        <div class="aspect-square rounded-xl overflow-hidden bg-muted border border-line mb-3">
          <img id="imgPreview" src="<?= e(product_image($form['image'])) ?>" class="w-full h-full object-cover">
        </div>
        <input name="image" type="file" accept="image/jpeg,image/png,image/webp" id="imgInput" class="block w-full text-sm text-body file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-cream file:cursor-pointer hover:file:bg-hover">
        <p class="text-xs text-body/60 mt-2">JPG / PNG / WEBP, maks 3 MB.</p>
      </div>

      <button type="submit" class="btn-primary w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft">
        <i class="fa-solid fa-floppy-disk mr-2"></i><?= $action === 'edit' ? 'Simpan Perubahan' : 'Tambah Produk' ?>
      </button>
    </div>
  </form>

  <script>
  document.getElementById('imgInput')?.addEventListener('change', (e) => {
    const f = e.target.files[0]; if (!f) return;
    document.getElementById('imgPreview').src = URL.createObjectURL(f);
  });
  </script>

<?php else:
// ============ LIST ============
$products = $pdo->query(
    "SELECT p.*, c.name AS category_name FROM products p
     LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC"
)->fetchAll();
?>
  <div class="flex items-center justify-between mb-5">
    <p class="text-sm text-body"><?= count($products) ?> produk</p>
    <a href="<?= url('admin/products', ['action' => 'new']) ?>" class="btn-primary inline-flex items-center gap-2 bg-primary hover:bg-hover text-cream text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-soft">
      <i class="fa-solid fa-plus"></i> Tambah Produk
    </a>
  </div>

  <div class="bg-card border border-line rounded-2xl shadow-soft overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 text-body text-left">
          <tr>
            <th class="px-5 py-3 font-medium">Produk</th>
            <th class="px-5 py-3 font-medium">Kategori</th>
            <th class="px-5 py-3 font-medium">Harga</th>
            <th class="px-5 py-3 font-medium">Stok</th>
            <th class="px-5 py-3 font-medium">Status</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          <?php if (!$products): ?>
            <tr><td colspan="6" class="px-5 py-10 text-center text-body">Belum ada produk. <a href="<?= url('admin/products', ['action'=>'new']) ?>" class="text-primary">Tambah sekarang</a>.</td></tr>
          <?php else: foreach ($products as $p):
            $badge = badge_meta($p['badge']); ?>
          <tr class="hover:bg-muted/30 transition">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <img src="<?= e(product_image($p['image'])) ?>" class="w-11 h-11 rounded-lg object-cover bg-muted">
                <div>
                  <p class="font-medium text-heading line-clamp-1"><?= e($p['name']) ?></p>
                  <?php if ($badge): ?><span class="badge <?= $badge[1] ?> text-[10px]"><?= e($badge[0]) ?></span><?php endif; ?>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 text-body"><?= e($p['category_name'] ?? '-') ?></td>
            <td class="px-5 py-3 font-medium text-heading"><?= rupiah($p['price']) ?></td>
            <td class="px-5 py-3"><?= (int) $p['stock'] > 0 ? (int) $p['stock'] : '<span class="text-danger">Habis</span>' ?></td>
            <td class="px-5 py-3">
              <?php if ($p['is_active']): ?><span class="status-pill status-done">Aktif</span><?php else: ?><span class="status-pill status-cancelled">Nonaktif</span><?php endif; ?>
            </td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-2">
                <a href="<?= url('product', ['id' => (int) $p['id']]) ?>" target="_blank" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-muted text-body transition" title="Lihat"><i class="fa-solid fa-eye"></i></a>
                <a href="<?= url('admin/products', ['action' => 'edit', 'id' => (int) $p['id']]) ?>" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-primary/10 text-primary transition" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="<?= url('admin/products') ?>" class="js-delete inline" data-name="<?= e($p['name']) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-danger/10 text-danger transition" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
  document.querySelectorAll('.js-delete').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      Swal.fire({
        title: 'Hapus produk?',
        text: form.dataset.name,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189',
        confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
      }).then((r) => { if (r.isConfirmed) form.submit(); });
    });
  });
  </script>
<?php endif; ?>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
