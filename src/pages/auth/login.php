<?php
/*
FILE: src/pages/auth/login.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Login admin — password_verify(), session, session_regenerate_id(), CSRF
*/
$activeNav = '';
$pageTitle = 'Login Admin';

// FRIZA - Jika sudah login, langsung ke dashboard
if (is_logged_in()) {
    redirect(url('admin'));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); // FRIZA - proteksi CSRF
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // FRIZA - Ambil admin & verifikasi hash password
        $stmt = db()->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // FRIZA - Cegah session fixation
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            flash('success', 'Selamat datang kembali, ' . $admin['name'] . '!');
            redirect(url('admin'));
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

$flashError = get_flash('error');
require SRC_PATH . '/includes/header.php';
?>
<section class="min-h-[70vh] grid place-items-center px-4 py-16">
  <div class="w-full max-w-md" data-aos="zoom-in">
    <div class="text-center mb-7">
      <span class="inline-grid place-items-center w-14 h-14 rounded-2xl bg-primary text-cream text-2xl shadow-soft mb-4">
        <i class="fa-solid fa-lock"></i>
      </span>
      <h1 class="font-display text-3xl text-heading">Panel Admin</h1>
      <p class="text-body mt-1 text-sm">Masuk untuk mengelola Lumiere Cookies</p>
    </div>

    <div class="bg-card border border-line rounded-2xl p-7 shadow-soft">
      <?php if ($error): ?>
        <div class="mb-5 rounded-xl border border-danger/30 bg-danger/10 text-danger px-4 py-3 text-sm">
          <i class="fa-solid fa-circle-exclamation mr-2"></i><?= e($error) ?>
        </div>
      <?php elseif ($flashError): ?>
        <div class="mb-5 rounded-xl border border-warning/30 bg-warning/10 text-warning px-4 py-3 text-sm">
          <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= e($flashError) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= url('login') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Username</label>
          <div class="relative">
            <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-body/50"></i>
            <input name="username" required autofocus
                   class="field w-full rounded-xl border border-line bg-cream/50 pl-11 pr-4 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="admin">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Password</label>
          <div class="relative">
            <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-body/50"></i>
            <input name="password" type="password" required
                   class="field w-full rounded-xl border border-line bg-cream/50 pl-11 pr-11 py-2.5 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="••••••••">
            <button type="button" id="togglePass" class="absolute right-3 top-1/2 -translate-y-1/2 text-body/50 hover:text-primary transition" aria-label="Lihat password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary w-full bg-primary hover:bg-hover text-cream font-semibold py-3 rounded-xl transition shadow-soft hover:scale-[1.02]">
          <i class="fa-solid fa-right-to-bracket mr-2"></i>Masuk
        </button>
      </form>

      <p class="text-center text-xs text-body/60 mt-5">
        Demo: <span class="font-mono text-body">admin / admin123</span>
      </p>
    </div>

    <a href="<?= url('home') ?>" class="block text-center text-sm text-accent hover:text-hover transition mt-5">
      <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke beranda
    </a>
  </div>
</section>

<script>
(function () {
  const btn = document.getElementById('togglePass');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const input = btn.closest('.relative').querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fa-solid fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fa-solid fa-eye'; }
  });
})();
</script>
<?php require SRC_PATH . '/includes/footer.php'; ?>
