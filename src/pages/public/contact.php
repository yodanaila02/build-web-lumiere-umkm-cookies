gi<?php
/*
FILE: src/pages/public/contact.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: Halaman kontak — form pesan (simpan ke contact_messages), peta, WhatsApp
*/
$activeNav = 'contact';
$pageTitle = 'Kontak';

$pdo     = db();
$errors  = [];
$success = false;
$old     = ['name' => '', 'email' => '', 'phone' => '', 'message' => ''];

// SEKAR - Simpan pesan kontak
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); // SEKAR - CSRF
    $old['name']    = trim((string) ($_POST['name'] ?? ''));
    $old['email']   = trim((string) ($_POST['email'] ?? ''));
    $old['phone']   = trim((string) ($_POST['phone'] ?? ''));
    $old['message'] = trim((string) ($_POST['message'] ?? ''));

    if ($old['name'] === '')    $errors[] = 'Nama wajib diisi.';
    if ($old['message'] === '') $errors[] = 'Pesan wajib diisi.';
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            "INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$old['name'], $old['email'] ?: null, $old['phone'] ?: null, $old['message']]);
        $success = true;
        $old = ['name' => '', 'email' => '', 'phone' => '', 'message' => ''];
    }
}

$mapsEmbed = setting('maps_embed');
$address   = setting('address');
$ig        = setting('instagram');
$igUrl     = setting('instagram_url', '#');
$waNumbers = whatsapp_numbers();

require SRC_PATH . '/includes/header.php';
?>
<section class="bg-muted/40 border-b border-line">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center" data-aos="fade-up">
    <span class="text-xs tracking-[0.3em] uppercase text-accent font-semibold">Hubungi Kami</span>
    <h1 class="font-display text-4xl sm:text-5xl text-heading mt-3">Mari Terhubung</h1>
    <p class="text-body mt-3 max-w-xl mx-auto">Punya pertanyaan, pesanan custom, atau kerja sama? Kirim pesan atau langsung chat WhatsApp.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid lg:grid-cols-2 gap-10">
  <!-- Form -->
  <div data-aos="fade-right">
    <?php if ($success): ?>
      <div class="mb-6 rounded-2xl border border-success/30 bg-success/10 text-success px-5 py-4">
        <i class="fa-solid fa-circle-check mr-2"></i>Terima kasih! Pesan Anda sudah kami terima dan akan segera dibalas.
      </div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="mb-6 rounded-2xl border border-danger/30 bg-danger/10 text-danger px-5 py-4 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
          <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= url('contact') ?>" class="bg-card border border-line rounded-2xl p-6 shadow-soft space-y-4">
      <?= csrf_field() ?>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Nama <span class="text-danger">*</span></label>
          <input name="name" value="<?= e($old['name']) ?>" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Nama Anda">
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">No. HP</label>
          <input name="phone" value="<?= e($old['phone']) ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="08xxxxxxxxxx">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-heading mb-1.5">Email</label>
        <input name="email" type="email" value="<?= e($old['email']) ?>" class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="email@contoh.com">
      </div>
      <div>
        <label class="block text-sm font-medium text-heading mb-1.5">Pesan <span class="text-danger">*</span></label>
        <textarea name="message" rows="5" required class="field w-full rounded-xl border border-line bg-cream/50 px-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="Tulis pesan Anda…"><?= e($old['message']) ?></textarea>
      </div>
      <button type="submit" class="btn-primary w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft hover:scale-[1.02]">
        <i class="fa-solid fa-paper-plane mr-2"></i>Kirim Pesan
      </button>
    </form>
  </div>

  <!-- Info + Map -->
  <div class="space-y-5" data-aos="fade-left">
    <div class="bg-card border border-line rounded-2xl p-6 shadow-soft">
      <h3 class="font-display text-xl text-heading mb-4">Informasi Kontak</h3>
      <ul class="space-y-4 text-sm">
        <li class="flex gap-3">
          <span class="w-9 h-9 grid place-items-center rounded-full bg-muted text-primary shrink-0"><i class="fa-solid fa-location-dot"></i></span>
          <span class="text-body"><?= e($address) ?></span>
        </li>
        <?php if ($ig): ?>
        <li class="flex gap-3 items-center">
          <span class="w-9 h-9 grid place-items-center rounded-full bg-muted text-primary shrink-0"><i class="fa-brands fa-instagram"></i></span>
          <a href="<?= e($igUrl) ?>" target="_blank" rel="noopener" class="text-body hover:text-primary transition"><?= e($ig) ?></a>
        </li>
        <?php endif; ?>
        <?php foreach ($waNumbers as $wa): ?>
        <li class="flex gap-3 items-center">
          <span class="w-9 h-9 grid place-items-center rounded-full bg-muted text-success shrink-0"><i class="fa-brands fa-whatsapp"></i></span>
          <a href="<?= e(wa_link($wa['number'], 'Halo Lumiere Cookies!')) ?>" target="_blank" rel="noopener" class="text-body hover:text-success transition">
            <?= e($wa['label']) ?> — <?= e($wa['number']) ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="rounded-2xl overflow-hidden border border-line shadow-soft aspect-[4/3] bg-muted">
      <?php if ($mapsEmbed): ?>
        <iframe src="<?= e($mapsEmbed) ?>" class="w-full h-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Lumiere Cookies"></iframe>
      <?php else: ?>
        <div class="w-full h-full grid place-items-center text-body">Peta belum tersedia</div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require SRC_PATH . '/includes/footer.php'; ?>
