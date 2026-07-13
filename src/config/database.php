<?php
/*
FILE: src/config/database.php
RESPONSIBLE MEMBER: FRIZA
FEATURE: Koneksi database MySQL menggunakan PDO + Prepared Statement
*/

// FRIZA - Memuat variabel lingkungan dari file .env (jika ada)
if (!function_exists('load_env')) {
    function load_env(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            // Buang tanda kutip pembungkus jika ada
            $value = trim($value, "\"'");
            if ($key !== '' && getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

load_env(dirname(__DIR__, 2) . '/.env');

// FRIZA - Helper ambil env dengan nilai default
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $val = getenv($key);
        return ($val === false || $val === '') ? $default : $val;
    }
}

/**
 * FRIZA - Mengembalikan satu instance koneksi PDO (singleton sederhana).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'lumiere_cookies');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die(
            '<div style="font-family:sans-serif;max-width:560px;margin:80px auto;padding:24px;'
            . 'border:1px solid #EADFD6;border-radius:16px;background:#FFF8F2;color:#2F241F">'
            . '<h2 style="margin-top:0">Gagal terhubung ke database</h2>'
            . '<p>Pastikan MySQL berjalan dan file <code>.env</code> sudah diisi dengan benar '
            . '(DB_HOST, DB_NAME, DB_USER, DB_PASS), serta <code>schema.sql</code> &amp; '
            . '<code>seed.sql</code> sudah dijalankan.</p>'
            . '<p style="color:#D97A6C"><strong>Detail:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
            . '</div>'
        );
    }

    return $pdo;
}
