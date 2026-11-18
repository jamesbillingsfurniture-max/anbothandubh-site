<?php
// Guest pressed "Pay": check everything again on the server, hold the dates, send them to Stripe.
require dirname(__DIR__) . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);
$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) json_out(['error' => 'Bad request'], 400);
if (!empty($in['website'])) json_out(['error' => 'Bad request'], 400); // spam trap

$clean = fn($k, $max) => trim(mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', ' ', (string) ($in[$k] ?? '')), 0, $max));
$data = [
    'arrive' => $clean('arrive', 10),
    'depart' => $clean('depart', 10),
    'guests' => (int) ($in['guests'] ?? 0),
    'pets' => !empty($in['pets']),
    'name' => $clean('name', 80),
    'email' => $clean('email', 120),
    'phone' => $clean('phone', 30),
    'arrival_time' => $clean('arrival_time', 20),
    'message' => trim(mb_substr((string) ($in['message'] ?? ''), 0, 600)),
    'rate_type' => ($in['rate_type'] ?? '') === 'nonref' ? 'nonref' : 'flexible',
];

$errors = [];
if ($data['guests'] < 1 || $data['guests'] > 2) $errors[] = 'Up to 2 guests.';
if (mb_strlen($data['name']) < 2) $errors[] = 'Please enter your name.';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if (!preg_match('/^[+0-9 ()-]{7,30}$/', $data['phone'])) $errors[] = 'Please enter a phone number.';
if (empty($in['agree'])) $errors[] = 'Please agree to the house rules and cancellation policy.';
if ($errors) json_out(['error' => implode(' ', $errors)], 422);

// Rate limit: max 8 checkout attempts per visitor per hour.
$s = db()->prepare("SELECT COUNT(*) FROM events WHERE name='checkout_attempt' AND visitor=? AND ts>?");
$s->execute([visitor_id(), time() - 3600]);
if ((int) $s->fetchColumn() >= 8) json_out(['error' => 'Too many attempts. Please try again later or message us on WhatsApp.'], 429);
record_event('checkout_attempt');

// Hold the dates inside a lock so two people can't grab the same nights at once.
$pdo = db();
$pdo->exec('BEGIN IMMEDIATE');
try {
    $err = validate_stay($data['arrive'], $data['depart']);
    if ($err) { $pdo->exec('ROLLBACK'); json_out(['error' => $err], 409); }
    $b = create_hold($data);
    $pdo->exec('COMMIT');
} catch (Throwable $e) {
    $pdo->exec('ROLLBACK');
    error_log('checkout hold: ' . $e->getMessage());
    json_out(['error' => 'Something went wrong. Please try again.'], 500);
}

if (is_demo()) {
    // Preview copy only: pretend the payment went through.
    mark_paid($b['ref'], 'demo', 'demo');
    json_out(['url' => '/book/thanks/?ref=' . $b['ref'], 'ref' => $b['ref']]);
}

try {
    $session = stripe_checkout_session($b);
    $pdo->prepare('UPDATE bookings SET stripe_session=? WHERE ref=?')->execute([$session['id'], $b['ref']]);
    json_out(['url' => $session['url'], 'ref' => $b['ref']]);
} catch (Throwable $e) {
    release_hold($b['ref']);
    error_log('stripe checkout: ' . $e->getMessage());
    json_out(['error' => 'Card payment is not available right now. Please try again shortly, or message us on WhatsApp.'], 502);
}
