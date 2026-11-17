<?php
// James's private page: upcoming stays, bookings, calendar links, weekly summary preview.
require dirname(__DIR__) . '/app/bootstrap.php';
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');
session_name('bd_admin');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS']), 'path' => '/admin/']);
session_start();

$pw = (string) cfg('admin_password');
$err = '';
if (isset($_POST['password'])) {
    usleep(400000);
    if (strlen($pw) >= 8 && hash_equals($pw, (string) $_POST['password'])) {
        session_regenerate_id(true);
        $_SESSION['ok'] = 1; $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header('Location: /admin/'); exit;
    }
    $err = strlen($pw) < 8 ? 'Set admin_password (8+ characters) in config.php first.' : 'Wrong password.';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: /admin/'); exit; }
$in = !empty($_SESSION['ok']);

if ($in && $_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
    $ref = (string) ($_POST['ref'] ?? '');
    if (($_POST['do'] ?? '') === 'cancel' && $ref) {
        db()->prepare("UPDATE bookings SET status='cancelled', cancelled_at=? WHERE ref=?")->execute([time(), $ref]);
    }
    if (($_POST['do'] ?? '') === 'resend' && $ref && ($b = get_booking($ref))) {
        send_booking_emails($b);
    }
    if (($_POST['do'] ?? '') === 'lockbox' && $ref && ($b = get_booking($ref))) {
        send_lockbox_email($b);
        db()->prepare('UPDATE bookings SET lockbox_sent_at=? WHERE ref=?')->execute([time(), $ref]);
    }
    header('Location: /admin/'); exit;
}

if ($in && isset($_GET['summary'])) { echo weekly_summary()['html']; exit; }
?><!doctype html><html lang="en-IE"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Admin | Bothán Dubh</title><link rel="stylesheet" href="/assets/style.css">
<style>.wrap{max-width:900px;padding-block:24px 60px}table{width:100%;border-collapse:collapse;font-size:14px}td,th{text-align:left;padding:8px 6px;border-bottom:1px solid var(--line);vertical-align:top}
.pill{font-size:12px;padding:2px 8px;border-radius:999px;background:var(--booked)}.pill.paid{background:var(--olive);color:var(--onaccent)}form.inline{display:inline}form.inline button{font:inherit;font-size:12px;padding:4px 8px;border-radius:6px;border:1px solid var(--line);background:var(--surface);color:var(--ink);cursor:pointer}
.box{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:16px;margin:16px 0}code{word-break:break-all;font-size:13px}.scroll{overflow-x:auto}</style></head><body><div class="wrap">
<h1 style="font-size:30px">Bothán Dubh admin</h1>
<?php if (!$in): ?>
  <form method="post" class="pay" style="max-width:320px"><label class="lbl" for="pw">Password</label><input id="pw" type="password" name="password" autofocus><?php if ($err): ?><div class="err"><?= h($err) ?></div><?php endif; ?><button class="btn">Log in</button></form>
<?php else:
    $csrf = $_SESSION['csrf'];
    $mode = is_demo() ? 'PREVIEW (no Stripe)' : (is_live() ? 'LIVE payments' : 'TEST mode (Stripe sandbox)');
    $cal = ical_refresh();
    $upcoming = db()->query("SELECT * FROM bookings WHERE status='paid' AND depart >= date('now') ORDER BY arrive")->fetchAll();
    $recent = db()->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 30")->fetchAll();
    $ical = array_filter(ical_events(), fn($e) => $e[2] >= date('Y-m-d'));
    usort($ical, fn($a, $b) => strcmp($a[1], $b[1]));
    $exportUrl = rtrim((string) cfg('base_url'), '/') . '/calendar.php?key=' . rawurlencode((string) cfg('ical_export_key'));
    $act = function (string $do, string $ref, string $label, string $confirm = '') use ($csrf) {
        return '<form class="inline" method="post"' . ($confirm ? ' onsubmit="return confirm(\'' . h($confirm) . '\')"' : '') . '><input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="ref" value="' . h($ref) . '"><input type="hidden" name="do" value="' . $do . '"><button>' . h($label) . '</button></form>';
    };
?>
  <p>Mode: <b><?= h($mode) ?></b> · <a href="?summary=1">Preview this week's Monday summary</a> · <a href="?logout=1">Log out</a></p>

  <div class="box"><h2 style="font-size:20px">Upcoming direct bookings</h2><div class="scroll"><table><tr><th>Dates</th><th>Guest</th><th>Paid</th><th>Emails</th><th></th></tr>
  <?php foreach ($upcoming as $b): ?><tr><td><?= h(nice_date($b['arrive'], false)) ?> → <?= h(nice_date($b['depart'], false)) ?><br><span class="small"><?= (int) $b['nights'] ?> nights · <?= h($b['ref']) ?></span></td>
  <td><?= h($b['name']) ?><br><span class="small"><?= h($b['email']) ?> · <?= h($b['phone']) ?><br><?= (int) $b['guests'] ?> guests<?= $b['pets'] ? ' + pets' : '' ?><?= $b['arrival_time'] ? ' · arriving ' . h($b['arrival_time']) : '' ?></span><?= $b['message'] ? '<br><i class="small">' . h($b['message']) . '</i>' : '' ?></td>
  <td><?= euro((int) $b['amount_cents']) ?></td><td class="small">Confirmation <?= $b['confirm_sent_at'] ? '✓' : '–' ?><br>Lockbox <?= $b['lockbox_sent_at'] ? '✓ ' . date('j M', (int) $b['lockbox_sent_at']) : 'not yet' ?></td>
  <td><?= $act('resend', $b['ref'], 'Resend confirmation') ?> <?= $act('lockbox', $b['ref'], 'Send lockbox email now') ?> <?= $act('cancel', $b['ref'], 'Cancel (frees dates)', 'Cancel this booking and free the dates? Refunds are done in Stripe.') ?></td></tr>
  <?php endforeach; if (!$upcoming): ?><tr><td colspan="5">No upcoming direct bookings.</td></tr><?php endif; ?></table></div>
  <p class="small">Refunds: open the payment in Stripe (Payments) and press Refund. Then cancel here to free the dates.</p></div>

  <div class="box"><h2 style="font-size:20px">Airbnb and Booking.com stays (from their calendars)</h2>
  <p class="small">Last download: <?php foreach ((array) cfg('ical_import') as $name => $url): $f = ical_cache_file($name); ?><?= h($name) ?> <?= !$url ? 'not set up' : (is_file($f) ? date('j M H:i', filemtime($f)) : 'never') ?><?= ($cal[$name] ?? true) ? '' : ' ⚠ failed' ?> · <?php endforeach; ?></p>
  <table><?php foreach (array_slice($ical, 0, 30) as $e): ?><tr><td><?= h(nice_date($e[1], false)) ?> → <?= h(nice_date($e[2], false)) ?></td><td><?= h($e[0]) ?></td><td class="small"><?= h($e[3]) ?></td></tr><?php endforeach; ?></table></div>

  <div class="box"><h2 style="font-size:20px">Your website calendar link</h2>
  <p>Paste this into Airbnb (Calendar › Availability › Connect calendars › Import) and Booking.com (Rates &amp; availability › Sync calendars) so direct bookings block those sites too:</p>
  <p><code><?= cfg('ical_export_key') ? h($exportUrl) : 'Set ical_export_key in config.php first.' ?></code></p></div>

  <div class="box"><h2 style="font-size:20px">All recent bookings</h2><div class="scroll"><table><tr><th>Created</th><th>Ref</th><th>Dates</th><th>Name</th><th>Amount</th><th>Status</th></tr>
  <?php foreach ($recent as $b): ?><tr><td><?= date('j M H:i', (int) $b['created_at']) ?></td><td><?= h($b['ref']) ?></td><td><?= h($b['arrive']) ?> → <?= h($b['depart']) ?></td><td><?= h($b['name']) ?></td><td><?= euro((int) $b['amount_cents']) ?></td><td><span class="pill <?= h($b['status']) ?>"><?= h($b['status'] === 'expired' ? 'not paid' : $b['status']) ?></span></td></tr><?php endforeach; ?></table></div>
  <p class="small">“Not paid” means the guest started paying but didn't finish; those dates were released automatically.</p></div>
<?php endif; ?>
</div></body></html>
