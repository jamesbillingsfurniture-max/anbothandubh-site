<?php
// Prices, availability, holds and confirmed bookings.

const HOLD_MINUTES = 35; // Stripe checkout lasts 30 minutes; keep dates held a little longer.

function night_rate(DateTimeImmutable $night): int
{
    $w = (int) $night->format('N'); // 1 Mon .. 7 Sun
    return $w >= 5 ? (int) cfg('rate_weekend') : (int) cfg('rate_weekday');
}

/** Price breakdown in cents for arrive..depart (depart not a night). */
function quote(string $arrive, string $depart, bool $pets): array
{
    $a = new DateTimeImmutable($arrive); $d = new DateTimeImmutable($depart);
    $lines = []; $total = 0; $n = 0;
    for ($x = $a; $x < $d; $x = $x->modify('+1 day')) {
        $r = night_rate($x); $lines[$r] = ($lines[$r] ?? 0) + 1; $total += $r * 100; $n++;
    }
    $petTotal = $pets ? $n * (int) cfg('pet_fee') * 100 : 0;
    ksort($lines);
    return ['nights' => $n, 'by_rate' => $lines, 'pet_cents' => $petTotal, 'total_cents' => $total + $petTotal];
}

function today(): DateTimeImmutable { return new DateTimeImmutable('today'); }

function first_arrival(): DateTimeImmutable { return today()->modify('+' . (int) cfg('min_days_notice') . ' day'); }
function last_departure(): DateTimeImmutable { return today()->modify('+' . (int) cfg('months_ahead') . ' month'); }

/** Expire unpaid holds whose time is up. */
function expire_holds(): void
{
    db()->prepare("UPDATE bookings SET status='expired' WHERE status='hold' AND hold_until < ?")->execute([time()]);
}

/** Nights (Y-m-d) taken by direct bookings: paid, plus holds still in progress. */
function direct_nights(?string $exceptRef = null): array
{
    expire_holds();
    $rows = db()->query("SELECT ref, arrive, depart FROM bookings WHERE status IN ('paid','hold')")->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        if ($exceptRef && $r['ref'] === $exceptRef) continue;
        for ($x = new DateTimeImmutable($r['arrive']); $x < new DateTimeImmutable($r['depart']); $x = $x->modify('+1 day')) {
            $out[$x->format('Y-m-d')] = true;
        }
    }
    return $out;
}

/** All blocked nights: imported calendars + direct bookings. Keys are Y-m-d. */
function blocked_nights(bool $refresh = false, ?string $exceptRef = null): array
{
    return direct_nights($exceptRef) + ical_blocked_nights($refresh);
}

/** Returns an error message, or null if the stay is allowed. */
function validate_stay(string $arrive, string $depart, ?array $blocked = null): ?string
{
    $re = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($re, $arrive) || !preg_match($re, $depart)) return 'Please choose your dates.';
    $a = new DateTimeImmutable($arrive); $d = new DateTimeImmutable($depart);
    if ($a->format('Y-m-d') !== $arrive || $d->format('Y-m-d') !== $depart) return 'Please choose your dates.';
    if ($a < first_arrival()) return 'That arrival date is too soon to book online. Please message us on WhatsApp.';
    if ($d > last_departure()) return 'We only take bookings up to ' . (int) cfg('months_ahead') . ' months ahead.';
    $n = (int) $a->diff($d)->format('%r%a');
    if ($n < (int) cfg('min_nights')) return 'Minimum stay is ' . (int) cfg('min_nights') . ' nights.';
    if ($n > (int) cfg('max_nights')) return 'For stays over ' . (int) cfg('max_nights') . ' nights, please message us on WhatsApp.';
    $blocked ??= blocked_nights(true);
    for ($x = $a; $x < $d; $x = $x->modify('+1 day')) {
        if (isset($blocked[$x->format('Y-m-d')])) return 'Sorry, some of those dates have just been booked. Please pick other dates.';
    }
    return null;
}

function new_ref(): string
{
    $alpha = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $s = 'BD-';
    for ($i = 0; $i < 6; $i++) $s .= $alpha[random_int(0, strlen($alpha) - 1)];
    return $s;
}

function get_booking(string $ref): ?array
{
    $s = db()->prepare('SELECT * FROM bookings WHERE ref=?'); $s->execute([$ref]);
    return $s->fetch() ?: null;
}

/** Create a short hold while the guest pays. Returns the booking row. Dates must already be validated. */
function create_hold(array $in): array
{
    $q = quote($in['arrive'], $in['depart'], (bool) $in['pets']);
    $ref = new_ref();
    db()->prepare('INSERT INTO bookings(ref,status,arrive,depart,nights,guests,pets,name,email,phone,arrival_time,message,amount_cents,created_at,hold_until)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([
        $ref, 'hold', $in['arrive'], $in['depart'], $q['nights'], $in['guests'], $in['pets'] ? 1 : 0,
        $in['name'], $in['email'], $in['phone'], $in['arrival_time'], $in['message'],
        $q['total_cents'], time(), time() + HOLD_MINUTES * 60,
    ]);
    return get_booking($ref);
}

/** Mark a booking paid (safe to call more than once) and send the emails. */
function mark_paid(string $ref, string $session = '', string $payment = ''): ?array
{
    $b = get_booking($ref);
    if (!$b) return null;
    if ($b['status'] !== 'paid') {
        db()->prepare("UPDATE bookings SET status='paid', paid_at=?, stripe_session=COALESCE(NULLIF(?,''),stripe_session), stripe_payment=? WHERE ref=? AND status<>'paid'")
            ->execute([time(), $session, $payment, $ref]);
        $b = get_booking($ref);
        // Safety net: did an Airbnb/Booking.com booking land on the same dates while they were paying?
        $clash = false;
        $ical = ical_blocked_nights(true);
        for ($x = new DateTimeImmutable($b['arrive']); $x < new DateTimeImmutable($b['depart']); $x = $x->modify('+1 day')) {
            if (isset($ical[$x->format('Y-m-d')])) { $clash = true; break; }
        }
        if (!$b['confirm_sent_at']) {
            send_booking_emails($b, $clash);
            db()->prepare('UPDATE bookings SET confirm_sent_at=? WHERE ref=?')->execute([time(), $ref]);
            $b = get_booking($ref);
        }
    }
    return $b;
}

function release_hold(string $ref): void
{
    db()->prepare("UPDATE bookings SET status='expired' WHERE ref=? AND status='hold'")->execute([$ref]);
}
