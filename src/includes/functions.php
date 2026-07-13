<?php
/*
FILE: src/includes/functions.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Kumpulan fungsi bantu — URL, format, settings, CSRF, auth, cart
*/

// ---------------------------------------------------------------------
// FRIZA - Output & URL helpers
// ---------------------------------------------------------------------

/** Escape HTML (cegah XSS). */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Bangun URL halaman aplikasi (routing berbasis query string -> bulletproof). */
function url(string $page = 'home', array $params = []): string
{
    $query = array_merge(['page' => $page], $params);
    return APP_BASE . '/index.php?' . http_build_query($query);
}

/** Path ke aset publik. */
function asset(string $path): string
{
    return APP_BASE . '/assets/' . ltrim($path, '/');
}

/** Path gambar upload dengan fallback placeholder bila kosong. */
function product_image(?string $file): string
{
    if ($file && is_file(UPLOAD_PRODUCTS . '/' . $file)) {
        return asset('images/products/' . $file);
    }
    return asset('images/placeholder.svg');
}

function gallery_image(?string $file): string
{
    if ($file && is_file(UPLOAD_GALLERY . '/' . $file)) {
        return asset('images/gallery/' . $file);
    }
    return asset('images/placeholder.svg');
}

function article_image(?string $file): string
{
    if ($file && is_file(UPLOAD_ARTICLES . '/' . $file)) {
        return asset('images/articles/' . $file);
    }
    return asset('images/placeholder.svg');
}

/** Format rupiah. */
function rupiah($amount): string
{
    return 'Rp ' . number_format((int) $amount, 0, ',', '.');
}

/** Redirect lalu hentikan eksekusi. */
function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/** Slug sederhana dari teks. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . substr(md5((string) microtime(true)), 0, 6);
}

// ---------------------------------------------------------------------
// FRIZA - Settings & WhatsApp (diambil dari database, tidak di-hardcode)
// ---------------------------------------------------------------------

/** Ambil satu setting situs (di-cache dalam satu request). */
function setting(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

/** Daftar nomor WhatsApp aktif. */
function whatsapp_numbers(): array
{
    try {
        return db()->query(
            'SELECT * FROM whatsapp_numbers WHERE is_active = 1 ORDER BY is_primary DESC, id ASC'
        )->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Nomor WhatsApp utama. */
function whatsapp_primary(): ?string
{
    $list = whatsapp_numbers();
    return $list[0]['number'] ?? null;
}

/** Bangun tautan wa.me dengan pesan ter-encode. */
function wa_link(?string $number, string $text = ''): string
{
    $number = preg_replace('/\D+/', '', (string) $number);
    $base = 'https://wa.me/' . $number;
    return $text !== '' ? $base . '?text=' . rawurlencode($text) : $base;
}

// ---------------------------------------------------------------------
// FRIZA - CSRF Protection
// ---------------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Field hidden CSRF untuk diselipkan ke dalam <form>. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verifikasi token CSRF dari POST; hentikan bila gagal. */
function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        die('Sesi keamanan tidak valid (CSRF). Silakan muat ulang halaman dan coba lagi.');
    }
}

// ---------------------------------------------------------------------
// FRIZA - Authentication & Session
// ---------------------------------------------------------------------

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function current_admin(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    static $admin = null;
    if ($admin === null) {
        $stmt = db()->prepare('SELECT id, name, username, role FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
    }
    return $admin;
}

/** Lindungi halaman admin: redirect ke login bila belum terautentikasi. */
function require_admin(): void
{
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu untuk mengakses panel admin.');
        redirect(url('login'));
    }
}

// ---------------------------------------------------------------------
// FRIZA - Flash message (notifikasi sekali tampil)
// ---------------------------------------------------------------------

function flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// ---------------------------------------------------------------------
// FRIZA - Shopping Cart (berbasis session, sumber data tetap dari DB)
// ---------------------------------------------------------------------

/** Ambil isi keranjang mentah: [product_id => qty]. */
function cart_get(): array
{
    return $_SESSION['cart'] ?? [];
}

/** Simpan keranjang. */
function cart_save(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

/** Total jumlah item (penjumlahan qty) -> untuk badge bulat. */
function cart_count(): int
{
    return array_sum(cart_get());
}

/**
 * Keranjang lengkap dengan data produk dari DB + total.
 * Mengembalikan: ['items'=>[], 'subtotal'=>int, 'count'=>int]
 */
function cart_detailed(): array
{
    $cart = cart_get();
    $result = ['items' => [], 'subtotal' => 0, 'count' => 0];

    if (empty($cart)) {
        return $result;
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT id, name, price, image, stock FROM products WHERE id IN ($placeholders) AND is_active = 1"
    );
    $stmt->execute($ids);
    $found = $stmt->fetchAll();

    foreach ($found as $p) {
        $qty = (int) ($cart[$p['id']] ?? 0);
        if ($qty < 1) {
            continue;
        }
        $lineSubtotal = $qty * (int) $p['price'];
        $result['items'][] = [
            'id'        => (int) $p['id'],
            'name'      => $p['name'],
            'price'     => (int) $p['price'],
            'qty'       => $qty,
            'stock'     => (int) $p['stock'],
            'image'     => product_image($p['image']),
            'subtotal'  => $lineSubtotal,
        ];
        $result['subtotal'] += $lineSubtotal;
        $result['count']    += $qty;
    }

    return $result;
}

// ---------------------------------------------------------------------
// FRIZA - Upload gambar aman (JPG/PNG/WEBP)
// ---------------------------------------------------------------------

/**
 * Proses upload gambar. Return nama file tersimpan atau null bila tidak ada
 * file. Lempar RuntimeException bila file tidak valid.
 */
function handle_image_upload(string $field, string $destDir): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal (kode ' . $file['error'] . ').');
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 3 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, atau WEBP.');
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }

    $name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $allowed[$mime];
    $target = rtrim($destDir, '/') . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Gagal menyimpan gambar ke server.');
    }
    return $name;
}

// ---------------------------------------------------------------------
// FRIZA - Pagination kecil
// ---------------------------------------------------------------------

function paginate(int $total, int $perPage, int $current): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $current = max(1, min($current, $pages));
    return [
        'total'   => $total,
        'pages'   => $pages,
        'current' => $current,
        'offset'  => ($current - 1) * $perPage,
        'perPage' => $perPage,
    ];
}

/** Label & warna untuk badge produk. */
function badge_meta(string $badge): ?array
{
    $map = [
        'best_seller' => ['Best Seller', 'badge-best'],
        'new'         => ['New', 'badge-new'],
        'limited'     => ['Limited', 'badge-limited'],
        'pre_order'   => ['Pre-Order', 'badge-preorder'],
    ];
    return $map[$badge] ?? null;
}

/** Label status order. */
function order_status_meta(string $status): array
{
    $map = [
        'pending'    => ['Menunggu', 'status-pending'],
        'processing' => ['Diproses', 'status-processing'],
        'done'       => ['Selesai', 'status-done'],
        'cancelled'  => ['Dibatalkan', 'status-cancelled'],
    ];
    return $map[$status] ?? ['Tidak diketahui', 'status-pending'];
}

// ---------------------------------------------------------------------
// FRIZA - Komponen kartu produk (dipakai ulang di beranda & katalog)
// ---------------------------------------------------------------------
function render_product_card(array $p, string $aos = ''): string
{
    $img    = product_image($p['image'] ?? null);
    $badge  = badge_meta($p['badge'] ?? 'none');
    $stock  = (int) ($p['stock'] ?? 0);
    $soldOut = $stock <= 0;
    $detailUrl = url('product', ['id' => (int) $p['id']]);
    $price  = rupiah($p['price']);
    $name   = e($p['name']);

    $badgeHtml = $badge
        ? '<span class="badge ' . $badge[1] . ' absolute top-3 left-3 shadow-soft">' . e($badge[0]) . '</span>'
        : '';

    $stockHtml = $soldOut
        ? '<span class="badge bg-line text-body absolute top-3 right-3">Habis</span>'
        : '';

    $cta = $soldOut
        ? '<button disabled class="w-9 h-9 grid place-items-center rounded-full bg-line text-body/50 cursor-not-allowed"><i class="fa-solid fa-ban"></i></button>'
        : '<button data-add-to-cart data-id="' . (int) $p['id'] . '" aria-label="Tambah ke keranjang"
             class="w-9 h-9 grid place-items-center rounded-full bg-primary hover:bg-hover text-cream shadow-soft btn-primary transition hover:scale-110"><i class="fa-solid fa-plus"></i></button>';

    $aosAttr = $aos !== '' ? ' data-aos="fade-up" data-aos-delay="' . e($aos) . '"' : '';

    return <<<HTML
<article class="product-card group bg-card border border-line rounded-2xl overflow-hidden shadow-soft"{$aosAttr}>
  <a href="{$detailUrl}" class="block relative aspect-square overflow-hidden bg-muted">
    <img src="{$img}" alt="{$name}" loading="lazy" class="w-full h-full object-cover">
    {$badgeHtml}{$stockHtml}
  </a>
  <div class="p-4">
    <a href="{$detailUrl}" class="block">
      <h3 class="font-medium text-heading leading-snug line-clamp-1 group-hover:text-hover transition">{$name}</h3>
    </a>
    <div class="flex items-center justify-between mt-3">
      <span class="font-display text-lg text-primary">{$price}</span>
      {$cta}
    </div>
  </div>
</article>
HTML;
}
