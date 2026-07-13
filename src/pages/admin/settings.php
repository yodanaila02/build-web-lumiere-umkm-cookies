<?php
/*
FILE: src/pages/admin/settings.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: Pengaturan situs (profil, lokasi, maps, tentang) + kelola nomor WhatsApp
*/
$pageTitle = 'Pengaturan';
$adminPage = 'admin/settings';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? 'settings';

    // SEKAR - Simpan pengaturan situs
    if ($do === 'settings') {
        $keys = ['store_name','tagline','instagram','instagram_url','address','maps_embed','maps_link','about','vision','mission','shipping_cost'];
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            if (array_key_exists($k, $_POST)) {
                $stmt->execute([$k, trim((string) $_POST[$k])]);
            }
        }
        flash('success', 'Pengaturan situs berhasil disimpan.');
        redirect(url('admin/settings'));
    }

    // SEKAR - Tambah nomor WhatsApp
    if ($do === 'wa_add') {
        $label  = trim((string) ($_POST['label'] ?? ''));
        $number = preg_replace('/\D+/', '', (string) ($_POST['number'] ?? ''));
        if ($label && $number) {
            $pdo->prepare("INSERT INTO whatsapp_numbers (label, number, is_active, is_primary) VALUES (?, ?, 1, 0)")->execute([$label, $number]);
            flash('success', 'Nomor WhatsApp ditambahkan.');
        } else {
            flash('error', 'Label dan nomor wajib diisi.');
        }
        redirect(url('admin/settings'));
    }

    // SEKAR - Hapus nomor
    if ($do === 'wa_delete') {
        $pdo->prepare("DELETE FROM whatsapp_numbers WHERE id = ?")->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Nomor dihapus.');
        redirect(url('admin/settings'));
    }

    // SEKAR - Jadikan utama
    if ($do === 'wa_primary') {
        $pdo->exec("UPDATE whatsapp_numbers SET is_primary = 0");
        $pdo->prepare("UPDATE whatsapp_numbers SET is_primary = 1, is_active = 1 WHERE id = ?")->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Nomor utama diperbarui.');
        redirect(url('admin/settings'));
    }
}

// Ambil semua setting saat ini
$s = [];
foreach ($pdo->query("SELECT setting_key, setting_value FROM settings") as $row) $s[$row['setting_key']] = $row['setting_value'];
$waNumbers = $pdo->query("SELECT * FROM whatsapp_numbers ORDER BY is_primary DESC, id")->fetchAll();

require SRC_PATH . '/includes/admin_header.php';

function fld($s, $k) { return e($s[$k] ?? ''); }
?>
<div class="grid lg:grid-cols-3 gap-6">
  <!-- Pengaturan situs -->
  <div class="lg:col-span-2">
    <form method="post" action="<?= url('admin/settings') ?>" class="space-y-6">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="settings">

      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
        <h2 class="font-display text-lg text-heading mb-4">Profil Toko</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-heading mb-1.5">Nama Toko</label><input name="store_name" value="<?= fld($s,'store_name') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
          <div><label class="block text-sm font-medium text-heading mb-1.5">Tagline</label><input name="tagline" value="<?= fld($s,'tagline') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
          <div><label class="block text-sm font-medium text-heading mb-1.5">Instagram (@handle)</label><input name="instagram" value="<?= fld($s,'instagram') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
          <div><label class="block text-sm font-medium text-heading mb-1.5">URL Instagram</label><input name="instagram_url" value="<?= fld($s,'instagram_url') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
          <div class="sm:col-span-2"><label class="block text-sm font-medium text-heading mb-1.5">Ongkos Kirim (Rp)</label><input name="shipping_cost" type="number" min="0" value="<?= fld($s,'shipping_cost') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
        </div>
      </div>

      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
        <h2 class="font-display text-lg text-heading mb-4">Lokasi & Peta</h2>
        <div class="space-y-4">
          <div><label class="block text-sm font-medium text-heading mb-1.5">Alamat</label><textarea name="address" rows="2" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= fld($s,'address') ?></textarea></div>
          <div><label class="block text-sm font-medium text-heading mb-1.5">URL Embed Peta <span class="text-xs text-body/60">(akhiri dengan &output=embed)</span></label><input name="maps_embed" value="<?= fld($s,'maps_embed') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
          <div><label class="block text-sm font-medium text-heading mb-1.5">URL Peta (tautan)</label><input name="maps_link" value="<?= fld($s,'maps_link') ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></div>
        </div>
      </div>

      <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
        <h2 class="font-display text-lg text-heading mb-4">Tentang Kami</h2>
        <div class="space-y-4">
          <div><label class="block text-sm font-medium text-heading mb-1.5">Deskripsi Tentang</label><textarea name="about" rows="3" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= fld($s,'about') ?></textarea></div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-heading mb-1.5">Visi</label><textarea name="vision" rows="2" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= fld($s,'vision') ?></textarea></div>
            <div><label class="block text-sm font-medium text-heading mb-1.5">Misi</label><textarea name="mission" rows="2" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"><?= fld($s,'mission') ?></textarea></div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-primary bg-primary hover:bg-hover text-cream font-semibold px-6 py-3 rounded-xl transition shadow-soft"><i class="fa-solid fa-floppy-disk mr-2"></i>Simpan Pengaturan</button>
    </form>
  </div>

  <!-- WhatsApp -->
  <div class="lg:col-span-1">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft sticky top-24">
      <h2 class="font-display text-lg text-heading mb-4">Nomor WhatsApp</h2>
      <div class="space-y-2 mb-5">
        <?php foreach ($waNumbers as $w): ?>
        <div class="flex items-center gap-2 p-3 rounded-xl border <?= $w['is_primary'] ? 'border-primary bg-primary/5' : 'border-line' ?>">
          <span class="w-9 h-9 grid place-items-center rounded-full bg-success/10 text-success shrink-0"><i class="fa-brands fa-whatsapp"></i></span>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-heading flex items-center gap-1.5"><?= e($w['label']) ?><?php if ($w['is_primary']): ?><span class="badge badge-best text-[10px]">Utama</span><?php endif; ?></p>
            <p class="text-xs text-body font-mono"><?= e($w['number']) ?></p>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <?php if (!$w['is_primary']): ?>
            <form method="post" action="<?= url('admin/settings') ?>"><?= csrf_field() ?><input type="hidden" name="do" value="wa_primary"><input type="hidden" name="id" value="<?= (int) $w['id'] ?>"><button class="w-7 h-7 grid place-items-center rounded-lg hover:bg-primary/10 text-primary transition" title="Jadikan utama"><i class="fa-solid fa-star text-xs"></i></button></form>
            <form method="post" action="<?= url('admin/settings') ?>" class="js-delete" data-name="<?= e($w['label']) ?>"><?= csrf_field() ?><input type="hidden" name="do" value="wa_delete"><input type="hidden" name="id" value="<?= (int) $w['id'] ?>"><button class="w-7 h-7 grid place-items-center rounded-lg hover:bg-danger/10 text-danger transition"><i class="fa-solid fa-trash-can text-xs"></i></button></form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <form method="post" action="<?= url('admin/settings') ?>" class="space-y-3 border-t border-line pt-4">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="wa_add">
        <input name="label" placeholder="Label (mis. Naila)" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        <input name="number" placeholder="62812xxxx" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        <button type="submit" class="w-full border border-primary text-primary hover:bg-primary hover:text-cream font-medium py-2.5 rounded-xl transition text-sm"><i class="fa-solid fa-plus mr-2"></i>Tambah Nomor</button>
      </form>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('.js-delete').forEach((form) => { form.addEventListener('submit', (e) => { e.preventDefault();
  Swal.fire({ title: 'Hapus nomor?', text: form.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then((r) => { if (r.isConfirmed) form.submit(); });
}); });
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
