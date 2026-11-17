<?php
// The emails: booking confirmation (guest), new booking alert (owner), lockbox details (guest).

function booking_rows(array $b): array
{
    $rows = [
        ['Booking reference', $b['ref']],
        ['Arrive', nice_date($b['arrive']) . ', check-in 3pm to 9pm'],
        ['Leave', nice_date($b['depart']) . ', check-out by 11am'],
        ['Nights', (string) $b['nights']],
        ['Guests', $b['guests'] . ($b['pets'] ? ', with pets' : '')],
    ];
    if ($b['arrival_time']) $rows[] = ['Expected arrival', $b['arrival_time']];
    $rows[] = ['Paid', euro((int) $b['amount_cents'])];
    return $rows;
}

function send_booking_emails(array $b, bool $clash = false): void
{
    $wa = 'https://wa.me/353863468725';
    $first = trim(explode(' ', (string) $b['name'])[0]);

    // Guest confirmation
    $title = 'You\'re booked, ' . $first;
    $intro = '<p>Thank you for booking An Bothán Dubh direct. Your stay is confirmed and paid.</p>';
    $after = '<p><b>What happens next.</b> Two days before you arrive we\'ll email you the lockbox code and directions. Check-in is self check-in from 3pm to 9pm.</p>'
        . '<p><b>Good to know.</b> The cabin runs on solar power: one socket, up to 500 watts, so please don\'t plug in high-power items like hair dryers or kettles (there\'s a kettle for the gas hob). No Wi-Fi or TV, but good phone signal. Our sheepdog Sadie and our cat live with us at our house and may wander over: please don\'t feed them or let them in.</p>'
        . '<p><b>Cancellation.</b> Free cancellation with a full refund within 24 hours of booking. After that, the booking is non-refundable.</p>'
        . '<p>Questions? Just reply to this email or <a href="' . $wa . '" style="color:#7A3D4B">message us on WhatsApp</a> (086 346 8725).</p>';
    $rows = booking_rows($b);
    send_mail($b['email'], "Booking confirmed: An Bothán Dubh, " . nice_date($b['arrive'], false) . ' (' . $b['ref'] . ')',
        email_layout($title, $intro, $rows, $after), email_text($title, $intro, $rows, $after));

    // Owner alert
    $title = 'New direct booking: ' . euro((int) $b['amount_cents']);
    $intro = ($clash ? '<p style="background:#FBE9E7;border:1px solid #C0392B;padding:10px;border-radius:8px"><b>Check for a double booking:</b> Airbnb or Booking.com shows a stay on some of these dates. Please check straight away.</p>' : '')
        . '<p>' . h($b['name']) . ' has booked and paid. The dates are now blocked on the website and in the calendar Airbnb and Booking.com import.</p>';
    $rows = array_merge([['Guest', $b['name']], ['Email', $b['email']], ['Phone', $b['phone']]], booking_rows($b));
    if ($b['message']) $rows[] = ['Message', $b['message']];
    $after = '<p style="font-size:13px;color:#5E5F5C">Reply to this email to answer the guest. The lockbox email goes out automatically 2 days before arrival.'
        . (is_live() ? '' : ' <b>TEST MODE: no real money was taken.</b>') . '</p>';
    send_mail((string) cfg('owner_email'), ($clash ? '⚠ CHECK: ' : '') . 'New booking ' . $b['ref'] . ': ' . nice_date($b['arrive'], false) . ', ' . $b['nights'] . ' nights',
        email_layout($title, $intro, $rows, $after), email_text($title, $intro, $rows, $after), $b['email']);
}

function send_lockbox_email(array $b): bool
{
    $first = trim(explode(' ', (string) $b['name'])[0]);
    $title = 'See you soon, ' . $first;
    $intro = '<p>Here\'s everything you need for your arrival on <b>' . h(nice_date($b['arrive'])) . '</b>.</p>';
    $rows = [
        ['Lockbox code', (string) cfg('lockbox_code') ?: '(ask us on WhatsApp)'],
        ['Check-in', 'From 3pm to 9pm'],
        ['Check-out', nice_date($b['depart']) . ' by 11am'],
        ['Address', 'Attymass, Ballina, Co. Mayo' . (cfg('eircode') ? ', ' . cfg('eircode') : '')],
    ];
    $eir = (string) cfg('eircode');
    $dir = $eir ? '<p><b>Directions.</b><br>Put the Eircode <b>' . h($eir) . '</b> into Google Maps or your sat nav and follow it. You\'ll see the cabin just before or just past that point. '
        . '<a href="https://www.google.com/maps/search/?api=1&amp;query=' . rawurlencode($eir) . '" style="color:#7A3D4B">Open in Google Maps</a></p>' : '';
    if (cfg('directions')) $dir .= '<p>' . nl2br(h((string) cfg('directions'))) . '</p>';
    $after = $dir
        . '<p><b>Parking.</b> Park in the space for one car, then follow the gravel path to the cabin. There are three low steps up to the deck.</p>'
        . '<p><b>Power.</b> One socket, up to 500 watts: fine for phones, laptops and cameras. Please don\'t plug in high-power items like hair dryers or kettles. There\'s a kettle for the gas hob, and cooking and heating run on gas.</p>'
        . '<p><b>House rules.</b> No smoking or vaping on or near the property, no parties, quiet hours 10pm to 7am. Dogs off the bed please.</p>'
        . '<p><b>Sadie and the cat.</b> Our sheepdog Sadie and our cat live with us at our house. Sadie is friendly and may come over to say hello. Please don\'t feed her or the cat, or let them into the cabin.</p>'
        . '<p>Running late or need anything? <a href="https://wa.me/353863468725" style="color:#7A3D4B">WhatsApp us</a> on 086 346 8725.</p>'
        . '<p>Enjoy the quiet, and if you take photos, tag them <b>#AnBothanDubh</b>.</p>';
    return send_mail($b['email'], 'Your arrival at An Bothán Dubh: lockbox code and directions',
        email_layout($title, $intro, $rows, $after), email_text($title, $intro, $rows, $after));
}

/** Send lockbox emails for paid bookings arriving within 2 days (run by cron). */
function send_due_lockbox_emails(): int
{
    $limit = today()->modify('+2 day')->format('Y-m-d');
    $s = db()->prepare("SELECT * FROM bookings WHERE status='paid' AND lockbox_sent_at IS NULL AND arrive <= ? AND arrive >= ?");
    $s->execute([$limit, today()->format('Y-m-d')]);
    $n = 0;
    foreach ($s->fetchAll() as $b) {
        if (send_lockbox_email($b)) {
            db()->prepare('UPDATE bookings SET lockbox_sent_at=? WHERE ref=?')->execute([time(), $b['ref']]);
            $n++;
        }
    }
    return $n;
}
