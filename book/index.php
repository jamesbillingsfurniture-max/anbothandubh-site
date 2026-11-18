<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
$cancelled = false;
if (!empty($_GET['cancelled']) && preg_match('/^BD-[A-Z0-9]{6}$/', $_GET['cancelled'])) {
    release_hold($_GET['cancelled']);
    $cancelled = true;
}
$p = [
    'path' => '/book/',
    'title' => 'Book An Bothán Dubh Direct | Off-Grid Cabin, Ballina, Mayo',
    'description' => 'Check availability and book An Bothán Dubh direct: from €' . base_rate('weekday') . ' a night, no cleaning fee and no booking-site service fee. Pets €10 a night.',
    'image' => '/photos/00.webp',
];
page_head($p);
?>
<div class="wrap">
  <div class="pagehead"><h1>Book your stay</h1><p>Pick your dates, see the total, and pay securely by card.</p></div>
  <div class="bookgrid">
    <div class="book" id="card" data-api="/api/" aria-live="polite">
      <?php if ($cancelled): ?><div class="err">Payment cancelled. Nothing was charged and your dates have been released.</div><?php endif; ?>
      <div class="loading">Loading the calendar…</div>
      <noscript><p>The booking calendar needs JavaScript. You can also <a href="<?= WA_URL ?>">message us on WhatsApp</a> to book.</p></noscript>
    </div>
    <div>
      <section style="border-top:0;padding-top:0">
        <h2>Before you book</h2>
        <div class="rules">
          <div><h3>Arrival</h3><p>Check-in 3pm to 9pm, self check-in with a lockbox. Please let us know your arrival time. Check-out by 11am.</p></div>
          <div><h3>House rules</h3><p>Up to 2 guests. Pets welcome. No smoking or vaping on or near the property. No parties. Quiet hours 10pm to 7am.</p></div>
          <div><h3>Pets</h3><p>€10 per night, however many pets you bring. Dogs off the bed, please.</p></div>
          <div><h3>Cancellation</h3><p>Choose your rate when you book.</p><p><b>Flexible:</b> full refund up to 5 days before check-in. After that, the first night is non-refundable and 50% of the other nights is refunded.</p><p><b>Non-refundable:</b> <?= nonref_pct() ?>% cheaper, no refund.</p><p>Both: free cancellation within 24 hours of booking if you booked at least 7 days before check-in.</p></div>
          <div><h3>Prices</h3><p>From €<?= base_rate('weekday') ?> a night Monday to Thursday, €<?= base_rate('weekend') ?> a night Friday to Sunday. Some dates have special prices. Minimum <?= (int) cfg('min_nights') ?> nights. No cleaning fee.</p></div>
          <div><h3>Payment</h3><p>Pay in full by card through Stripe. You get a confirmation email straight away, and the lockbox code 2 days before you arrive.</p></div>
        </div>
      </section>
      <p class="small">Dates already booked on Airbnb or Booking.com are greyed out, so the same night can't be booked twice. Questions? <a href="<?= WA_URL ?>" target="_blank" rel="noopener">Message us on WhatsApp</a>.</p>
    </div>
  </div>
</div>
<?php page_foot($p, ['/assets/book.js']);
