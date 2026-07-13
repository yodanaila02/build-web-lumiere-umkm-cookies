<?php
/*
FILE: src/pages/api/cart.php
RESPONSIBLE MEMBER: NAILA YODA
FEATURE: Endpoint AJAX keranjang (add / update / remove / clear / get) -> JSON
*/

header('Content-Type: application/json; charset=utf-8');

// NAILA YODA - Helper kirim JSON lalu berhenti
function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// NAILA YODA - Terima payload dari form-urlencoded ($_POST) ATAU body JSON
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    if ($raw !== '' && $raw !== false) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = $json;
        }
    }
}

$action = $input['action'] ?? $_GET['action'] ?? 'get';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

// NAILA YODA - Aksi yang mengubah data wajib POST
if (in_array($action, ['add', 'update', 'remove', 'clear'], true) && !$isPost) {
    json_out(['ok' => false, 'message' => 'Metode harus POST.'], 405);
}

$cart = cart_get();

switch ($action) {
    // ----- ADD TO CART -----
    case 'add':
        $id  = (int) ($input['id'] ?? 0);
        $qty = max(1, (int) ($input['qty'] ?? 1));
        if ($id < 1) {
            json_out(['ok' => false, 'message' => 'Produk tidak valid.'], 422);
        }
        // Validasi produk benar-benar ada & aktif (sumber data dari DB)
        $stmt = db()->prepare('SELECT id, name, stock FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) {
            json_out(['ok' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }
        $newQty = ($cart[$id] ?? 0) + $qty;
        // Batasi sesuai stok (jika ada stok)
        if ((int) $product['stock'] > 0) {
            $newQty = min($newQty, (int) $product['stock']);
        }
        $cart[$id] = $newQty;
        cart_save($cart);
        json_out([
            'ok'       => true,
            'message'  => $product['name'] . ' ditambahkan ke keranjang',
            'added_id' => $id,
        ] + cart_detailed());
        break;

    // ----- UPDATE QTY -----
    case 'update':
        $id  = (int) ($input['id'] ?? 0);
        $qty = (int) ($input['qty'] ?? 0);
        if (isset($cart[$id])) {
            if ($qty < 1) {
                unset($cart[$id]);
            } else {
                $stmt = db()->prepare('SELECT stock FROM products WHERE id = ?');
                $stmt->execute([$id]);
                $stock = (int) ($stmt->fetchColumn() ?: 0);
                $cart[$id] = $stock > 0 ? min($qty, $stock) : $qty;
            }
            cart_save($cart);
        }
        json_out(['ok' => true] + cart_detailed());
        break;

    // ----- REMOVE -----
    case 'remove':
        $id = (int) ($input['id'] ?? 0);
        unset($cart[$id]);
        cart_save($cart);
        json_out(['ok' => true, 'message' => 'Item dihapus dari keranjang'] + cart_detailed());
        break;

    // ----- CLEAR -----
    case 'clear':
        cart_save([]);
        json_out(['ok' => true] + cart_detailed());
        break;

    // ----- GET (default) -----
    case 'get':
    default:
        json_out(['ok' => true] + cart_detailed());
        break;
}
