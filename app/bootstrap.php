<?php
// Shared setup for every page, API endpoint and cron job.
declare(strict_types=1);
date_default_timezone_set('Europe/Dublin');

// The hosting runs PHP 7.4: small stand-ins for PHP 8 functions.
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $h, string $n): bool { return $n === '' || strncmp($h, $n, strlen($n)) === 0; }
}

define('APP_DIR', __DIR__);
define('SITE_DIR', dirname(__DIR__));
define('DATA_DIR', getenv('BD_DATA_DIR') ?: SITE_DIR . '/data');

function cfg(?string $key = null)
{
    static $c = null;
    if ($c === null) {
        $c = require APP_DIR . '/config.sample.php';
        $file = getenv('BD_CONFIG') ?: APP_DIR . '/config.php';
        if (is_file($file)) {
            $c = array_replace($c, require $file);
        } else {
            $c['demo'] = true; // no config yet: never touch Stripe
        }
    }
    return $key === null ? $c : ($c[$key] ?? null);
}

function is_live(): bool { return cfg('mode') === 'live' && !cfg('demo'); }
function is_demo(): bool { return (bool) cfg('demo'); }

function h($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
    $pdo = new PDO('sqlite:' . DATA_DIR . '/site.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ref TEXT UNIQUE NOT NULL,
        status TEXT NOT NULL,            -- hold | paid | cancelled | expired
        arrive TEXT NOT NULL, depart TEXT NOT NULL, nights INTEGER NOT NULL,
        guests INTEGER NOT NULL, pets INTEGER NOT NULL DEFAULT 0,
        name TEXT, email TEXT, phone TEXT, arrival_time TEXT, message TEXT,
        amount_cents INTEGER NOT NULL,
        stripe_session TEXT, stripe_payment TEXT,
        created_at INTEGER NOT NULL, hold_until INTEGER, paid_at INTEGER,
        confirm_sent_at INTEGER, lockbox_sent_at INTEGER, cancelled_at INTEGER)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS hits(
        id INTEGER PRIMARY KEY AUTOINCREMENT, ts INTEGER NOT NULL, day TEXT NOT NULL,
        path TEXT, device TEXT, source TEXT, visitor TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS events(
        id INTEGER PRIMARY KEY AUTOINCREMENT, ts INTEGER NOT NULL, day TEXT NOT NULL,
        name TEXT NOT NULL, visitor TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS kv(k TEXT PRIMARY KEY, v TEXT)");
    // Special prices for date ranges (start..end_excl), and owner-blocked dates.
    $pdo->exec("CREATE TABLE IF NOT EXISTS rates(id INTEGER PRIMARY KEY AUTOINCREMENT, start TEXT NOT NULL, end_excl TEXT NOT NULL,
        price INTEGER NOT NULL, label TEXT, offer INTEGER NOT NULL DEFAULT 0, min_nights INTEGER, created_at INTEGER)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS blocks(id INTEGER PRIMARY KEY AUTOINCREMENT, start TEXT NOT NULL, end_excl TEXT NOT NULL, note TEXT, created_at INTEGER)");
    $cols = array_column($pdo->query('PRAGMA table_info(bookings)')->fetchAll(), 'name');
    foreach (['thanks_sent_at' => 'INTEGER', 'rate_type' => "TEXT NOT NULL DEFAULT 'flexible'", 'notes' => 'TEXT'] as $c => $t) {
        if (!in_array($c, $cols, true)) $pdo->exec("ALTER TABLE bookings ADD COLUMN $c $t");
    }
    $pdo->exec("CREATE INDEX IF NOT EXISTS hits_day ON hits(day)");
    return $pdo;
}

function kv_get(string $k): ?string
{
    $s = db()->prepare('SELECT v FROM kv WHERE k=?'); $s->execute([$k]);
    $v = $s->fetchColumn(); return $v === false ? null : $v;
}
function kv_set(string $k, string $v): void
{
    db()->prepare('INSERT INTO kv(k,v) VALUES(?,?) ON CONFLICT(k) DO UPDATE SET v=excluded.v')->execute([$k, $v]);
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

function euro(int $cents): string
{
    return '€' . ($cents % 100 ? number_format($cents / 100, 2) : number_format($cents / 100));
}

function nice_date(string $ymd, bool $year = true): string
{
    $d = new DateTimeImmutable($ymd);
    return $d->format($year ? 'D j M Y' : 'D j M');
}

require_once APP_DIR . '/booking.php';
require_once APP_DIR . '/ical.php';
require_once APP_DIR . '/mailer.php';
require_once APP_DIR . '/emails.php';
require_once APP_DIR . '/stripe.php';
require_once APP_DIR . '/analytics.php';
