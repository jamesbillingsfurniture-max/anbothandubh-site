<?php
require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_DIR . '/layout.php';

$ref = preg_match('/^BD-[A-Z0-9]{6}$/', $_GET['ref'] ?? '') ? $_GET['ref'] : '';
$b = $ref ? get_booking($ref) : null;

// Confirm with Stripe straight away (the webhook does the same; whichever comes first wins).
if ($b && $b['status'] === 'hold' && !is_demo() && !empty($_GET['session_id'])) {
    try {
        $s = stripe_request('GET', 'checkout/sessions/' . rawurlencode($_GET['session_id']));
        if (($s['client_reference_id'] ?? '') === $ref && ($s['payment_status'] ?? '') === 'paid') {
            $b = mark_paid($ref, $s['id'], (string) ($s['payment_intent'] ?? ''));
            record_event('paid');
        }
    } catch (Throwable $e) { error_log('thanks page: ' . $e->getMessage()); }
}

$p = ['path' => '/book/thanks/', 'title' => 'Thank you | An Bothán Dubh', 'description' => 'Booking confirmation.', 'noindex' => true];
page_head($p);
?>
<div class="wrap" style="max-width:640px;padding-block:24px 64px">
<?php if ($b && $b['status'] === 'paid'): ?>
  <div class="book" style="position:static">
    <div class="done"><div class="tick">✓</div><h1 style="font-size:30px">You're booked, <?= h(explode(' ', $b['name'])[0]) ?></h1>
    <p><?= h(nice_date($b['arrive'])) ?> to <?= h(nice_date($b['depart'])) ?> · <?= (int) $b['nights'] ?> nights · <?= euro((int) $b['amount_cents']) ?> paid</p>
    <p class="small">Booking reference <b><?= h($b['ref']) ?></b></p></div>
    <p>We've emailed your confirmation to <b><?= h($b['email']) ?></b>. The lockbox code and directions will follow 2 days before you arrive.</p>
    <p>Check-in is from 3pm to 9pm. If anything changes, <a href="<?= WA_URL ?>">message us on WhatsApp</a>.</p>
    <a class="cta" href="/things-to-do-ballina/">Plan your trip: things to do nearby</a>
  </div>
<?php elseif ($b && $b['status'] === 'hold'): ?>
  <div class="book" style="position:static">
    <h1 style="font-size:28px">Almost there</h1>
    <p>We're waiting for the payment confirmation from Stripe. This usually takes a few seconds.</p>
    <p><a class="cta" href="">Refresh this page</a></p>
    <p class="small">If you were charged and this doesn't update, <a href="<?= WA_URL ?>">message us on WhatsApp</a> with reference <?= h($b['ref']) ?>.</p>
  </div>
<?php else: ?>
  <div class="book" style="position:static">
    <h1 style="font-size:28px">Booking not found</h1>
    <p>We couldn't find that booking. If you've paid, check your email for the confirmation, or <a href="<?= WA_URL ?>">message us on WhatsApp</a>.</p>
    <a class="cta" href="/book/">Back to booking</a>
  </div>
<?php endif; ?>
</div>
<?php page_foot($p);
