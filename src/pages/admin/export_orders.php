<?php
/*
FILE: src/pages/admin/export_orders.php
RESPONSIBLE MEMBER: NAYLA
FEATURE: Export laporan penjualan ke CSV (download)
*/
require_admin(); // NAYLA - hanya admin

$pdo = db();

// NAYLA - Filter status opsional
$statusFilter = $_GET['status'] ?? 'all';
$where  = '';
$params = [];
if (in_array($statusFilter, ['pending', 'processing', 'done', 'cancelled'], true)) {
    $where = 'WHERE status = ?';
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("SELECT * FROM orders $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// NAYLA - Kirim header download CSV
$filename = 'laporan-penjualan-' . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8

fputcsv($out, ['Invoice', 'Nama', 'No HP', 'Metode', 'Alamat', 'Link Maps', 'Subtotal', 'Ongkir', 'Total', 'Status', 'Catatan', 'Tanggal']);

$methodMap = ['pickup' => 'ambil sesuai jadwal', 'delivery' => 'Dikirim', 'other' => 'Lainnya'];
$statusMap = ['pending' => 'Menunggu', 'processing' => 'Diproses', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

$grandTotal = 0;
foreach ($orders as $o) {
    fputcsv($out, [
        $o['invoice_code'],
        $o['customer_name'],
        $o['customer_phone'],
        $methodMap[$o['delivery_method']] ?? $o['delivery_method'],
        $o['address'] ?? '-',
        $o['maps_link'] ?? '',
        $o['subtotal'],
        $o['shipping_cost'],
        $o['total'],
        $statusMap[$o['status']] ?? $o['status'],
        $o['notes'] ?? '',
        date('Y-m-d H:i', strtotime($o['created_at'])),
    ]);
    if ($o['status'] === 'done') $grandTotal += (int) $o['total'];
}

fputcsv($out, []);
fputcsv($out, ['', '', '', '', '', '', '', '', '', '', 'Total Pendapatan (Selesai)', $grandTotal]);
fclose($out);
exit;
