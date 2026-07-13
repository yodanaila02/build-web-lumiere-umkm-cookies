<?php
/*
FILE: src/pages/admin/messages.php
RESPONSIBLE MEMBER: SEKAR
FEATURE: Kotak masuk pesan kontak — tandai dibaca, balas via WhatsApp, hapus
*/
$pageTitle = 'Pesan Masuk';
$adminPage = 'admin/messages';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? '';
    $msgId = (int) ($_POST['id'] ?? 0);

    if ($do === 'delete') {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$msgId]);
        flash('success', 'Pesan dihapus.');
    } elseif ($do === 'read') {
        $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$msgId]);
    } elseif ($do === 'unread') {
        $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?")->execute([$msgId]);
    }
    redirect(url('admin/messages'));
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC")->fetchAll();
$unread = 0;
foreach ($messages as $m) if (!$m['is_read']) $unread++;

require SRC_PATH . '/includes/admin_header.php';
?>
<p class="text-sm text-body mb-5"><?= count($messages) ?> pesan · <span class="text-primary font-medium"><?= $unread ?> belum dibaca</span></p>

<div class="space-y-3">
  <?php if (!$messages): ?>
    <div class="bg-card border border-line rounded-2xl p-10 text-center text-body shadow-soft">Belum ada pesan masuk.</div>
  <?php else: foreach ($messages as $m): ?>
  <div class="bg-card border <?= $m['is_read'] ? 'border-line' : 'border-primary/40 bg-primary/[0.03]' ?> rounded-2xl p-5 shadow-soft">
    <div class="flex items-start justify-between gap-3 flex-wrap">
      <div class="flex-1 min-w-[200px]">
        <div class="flex items-center gap-2 flex-wrap">
          <p class="font-medium text-heading"><?= e($m['name']) ?></p>
          <?php if (!$m['is_read']): ?><span class="badge badge-new">Baru</span><?php endif; ?>
        </div>
        <p class="text-xs text-body mt-0.5">
          <?php if ($m['email']): ?><i class="fa-solid fa-envelope mr-1"></i><?= e($m['email']) ?><?php endif; ?>
          <?php if ($m['phone']): ?><span class="ml-3"><i class="fa-solid fa-phone mr-1"></i><?= e($m['phone']) ?></span><?php endif; ?>
          <span class="ml-3"><i class="fa-regular fa-clock mr-1"></i><?= date('d M Y H:i', strtotime($m['created_at'])) ?></span>
        </p>
        <p class="text-sm text-body mt-3 bg-muted/40 rounded-xl p-3"><?= nl2br(e($m['message'])) ?></p>
      </div>
      <div class="flex items-center gap-1.5 shrink-0">
        <?php if ($m['phone']): ?>
        <a href="<?= e(wa_link($m['phone'], 'Halo ' . $m['name'] . ', terima kasih sudah menghubungi Lumiere Cookies.')) ?>" target="_blank" rel="noopener" class="w-8 h-8 grid place-items-center rounded-lg hover:bg-success/10 text-success transition" title="Balas WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        <?php endif; ?>
        <form method="post" action="<?= url('admin/messages') ?>" class="inline">
          <?= csrf_field() ?><input type="hidden" name="do" value="<?= $m['is_read'] ? 'unread' : 'read' ?>"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
          <button class="w-8 h-8 grid place-items-center rounded-lg hover:bg-muted text-body transition" title="<?= $m['is_read'] ? 'Tandai belum dibaca' : 'Tandai dibaca' ?>"><i class="fa-solid <?= $m['is_read'] ? 'fa-envelope' : 'fa-envelope-open' ?>"></i></button>
        </form>
        <form method="post" action="<?= url('admin/messages') ?>" class="js-delete inline" data-name="pesan dari <?= e($m['name']) ?>">
          <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
          <button class="w-8 h-8 grid place-items-center rounded-lg hover:bg-danger/10 text-danger transition"><i class="fa-solid fa-trash-can"></i></button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>
<script>
document.querySelectorAll('.js-delete').forEach((form) => { form.addEventListener('submit', (e) => { e.preventDefault();
  Swal.fire({ title: 'Hapus pesan?', text: form.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#D9776A', cancelButtonColor: '#9C9189', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then((r) => { if (r.isConfirmed) form.submit(); });
}); });
</script>
<?php require SRC_PATH . '/includes/admin_footer.php'; ?>
