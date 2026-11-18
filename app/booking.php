<?php
// Prices, availability, holds, confirmed bookings, refunds and date changes.

const HOLD_MINUTES = 35; // Stripe checkout lasts 30 minutes; keep dates held a little longer.

/* ---------- Prices ---------- */

/** Normal nightly price: set in /admin/ (stored in the database), otherwise from the settings file. */
function base_rate(string $which): int
{
    $v = kv_get('rate_' . $which);
    return $v !== null && $v !== '' ? (int) $v : (int) cfg('rate_' . $which);
}

/** Special price ranges from /admin/, newest first (newest wins if two overlap). */
function special_rates(bool $fresh = false): array
{
    static $rows = null;
    if ($rows === null || $fresh) $rows = db()->query('SELECT * FROM rates ORDER BY id DESC')->fetchAll();
    return $rows;
}

/** Everything about one night: price, normal price, offer flag, label, minimum stay. */
function night_info(DateTimeImmutable $night): array
{
    $w = (int) $night->format('N'); // 1 Mon .. 7 Sun
    $base = $w >= 5 ? base_rate('weekend') : base_rate('weekday');
    $ymd = $night->format('Y-m-d');
    foreach (special_rates() as $r) {
        if ($ymd >= $r['start'] && $ymd < $r['end_excl']) {
            return ['price' => (int) $r['price'], 'base' => $base, 'offer' => (bool) $r['offer'] && (int) $r['price'] < $base,
                'label' => (string) $r['label'], 'min' => $r['min_nights'] ? (int) $r['min_nights'] : null];
        }
    }
    return ['price' => $base, 'base' => $base, 'offer' => false, 'label' => '', 'min' => null];
}

function night_rate(DateTimeImmutable $night): int { return night_info($night)['price']; }

function nonref_pct(): int { return max(0, min(50, (int) cfg('nonref_discount'))); }

/** Price breakdown in cents for arrive..depart (depart is not a night). */
function quote(string $arrive, string $depart, bool $pets, string $rateType = 'flexible'): array
{
    $a = new DateTimeImmutable($arrive); $d = new DateTimeImmutable($depart);
    $lines = []; $sub = 0; $n = 0; $saved = 0;
    for ($x = $a; $x < $d; $x = $x->modify('+1 day')) {
        $i = night_info($x);
        $lines[$i['price']] = ($lines[$i['price']] ?? 0) + 1;
        $sub += $i['price'] * 100; $n++;
        if ($i['offer']) $saved += ($i['base'] - $i['price']) * 100;
    }
    $petTotal = $pets ? $n * (int) cfg('pet_fee') * 100 : 0;
    $before = $sub + $petTotal;
    $disc = $rateType === 'nonref' ? (int) round($before * nonref_pct() / 100) : 0;
    ksort($lines);
    return ['nights' => $n, 'by_rate' => $lines, 'pet_cents' => $petTotal, 'offer_saved_cents' => $saved,
        'nonref_cents' => $disc, 'total_cents' => $before - $disc, 'rate_type' => $rateType];
}

function today(): DateTimeImmutable { return new DateTimeImmutable('today'); }

function first_arrival(): DateTimeImmutable { return today()->modify('+' . (int) cfg('min_days_notice') . ' day'); }
function last_departure(): DateTimeImmutable { return today()->modify('+' . (int) cfg('months_ahead') . ' month'); }

/* ---------- Availability ---------- */

/** Expire unpaid holds whose time is up. */
function expire_holds(): void
{
    db()->prepare("UPDATE bookings SET status='expired' WHERE status='hold' AND hold_until < ?")->execute([time()]);
}

function nights_between(string $a, string $d): array
{
    $out = [];
    for ($x = new DateTimeImmutable($a); $x < new DateTimeImmutable($d); $x = $x->modify('+1 day')) $out[$x->format('Y-m-d')] = true;
    return $out;
}

/** Nights (Y-m-d) taken by direct bookings: paid, plus holds still in progress. */
function direct_nights(?string $exceptRef = null): array
{
    expire_holds();
    $out = [];
    foreach (db()->query("SELECT ref, arrive, depart FROM bookings WHERE status IN ('paid','hold')")->fetchAll() as $r) {
        if ($exceptRef && $r['ref'] === $exceptRef) continue;
        $out += nights_between($r['arrive'], $r['depart']);
    }
    return $out;
}

/** Nights James blocked in /admin/. */
function owner_block_nights(): array
{
    $out = [];
    foreach (db()->query('SELECT start, end_excl FROM blocks')->fetchAll() as $r) $out += nights_between($r['start'], $r['end_excl']);
    return $out;
}

/** All blocked nights: imported calendars + direct bookings + owner blocks. Keys are Y-m-d. */
function blocked_nights(bool $refresh = false, ?string $exceptRef = null): array
{
    return direct_nights($exceptRef) + owner_block_nights() + ical_blocked_nights($refresh);
}

/** Minimum nights for a stay: the normal minimum, or more if a special price range asks for it. */
function min_nights_for(string $arrive, string $depart): int
{
    $min = (int) cfg('min_nights');
    foreach (array_keys(nights_between($arrive, $depart)) as $ymd) {
        $m = night_info(new DateTimeImmutable($ymd))['min'];
        if ($m && $m > $min) $min = $m;
    }
    return $min;
}

/** Returns an error message, or null if the stay is allowed. */
function validate_stay(string $arrive, string $depart, ?array $blocked = null, bool $admin = false, ?string $exceptRef = null): ?string
{
    $re = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($re, $arrive) || !preg_match($re, $depart)) return 'Please choose your dates.';
    $a = new DateTimeImmutable($arrive); $d = new DateTimeImmutable($depart);
    if ($a->format('Y-m-d') !== $arrive || $d->format('Y-m-d') !== $depart) return 'Please choose your dates.';
    $n = (int) $a->diff($d)->format('%r%a');
    if ($n < 1) return 'The departure date must be after the arrival date.';
    if (!$admin) {
        if ($a < first_arrival()) return 'That arrival date is too soon to book online. Please message us on WhatsApp.';
        if ($d > last_departure()) return 'We only take bookings up to ' . (int) cfg('months_ahead') . ' months ahead.';
        $min = min_nights_for($arrive, $depart);
        if ($n < $min) return 'Minimum stay for those dates is ' . $min . ' nights.';
        if ($n > (int) cfg('max_nights')) return 'For stays of ' . ((int) cfg('max_nights') + 1) . ' nights or more, please message us on WhatsApp.';
    }
    $blocked = $blocked ?? blocked_nights(true, $exceptRef);
    foreach (array_keys(nights_between($arrive, $depart)) as $ymd) {
        if (isset($blocked[$ymd])) return $admin ? "$ymd is already booked or blocked." : 'Sorry, some of those dates have just been booked. Please pick other dates.';
    }
    return null;
}

/* ---------- Bookings ---------- */

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
    $rateType = ($in['rate_type'] ?? 'flexible') === 'nonref' ? 'nonref' : 'flexible';
    $q = quote($in['arrive'], $in['depart'], (bool) $in['pets'], $rateType);
    $ref = new_ref();
    db()->prepare('INSERT INTO bookings(ref,status,arrive,depart,nights,guests,pets,name,email,phone,arrival_time,message,amount_cents,created_at,hold_until,rate_type)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([
        $ref, 'hold', $in['arrive'], $in['depart'], $q['nights'], $in['guests'], $in['pets'] ? 1 : 0,
        $in['name'], $in['email'], $in['phone'], $in['arrival_time'], $in['message'],
        $q['total_cents'], time(), time() + HOLD_MINUTES * 60, $rateType,
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
        $ical = ical_blocked_nights(true);
        $clash = (bool) array_intersect_key(nights_between($b['arrive'], $b['depart']), $ical);
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

/* ---------- Cancellation policy ---------- */

/** Plain-words policy for a rate, used on the site and in emails. */
function policy_text(string $rateType): string
{
    if ($rateType === 'nonref') {
        return 'Non-refundable rate (' . nonref_pct() . '% off): no refund if you cancel, except within 24 hours of booking when you booked at least 7 days before check-in.';
    }
    return 'Flexible rate: free cancellation with a full refund up to 5 days before check-in. After that, the first night is non-refundable and 50% of the other nights is refunded. Free cancellation within 24 hours of booking if you booked at least 7 days before check-in.';
}

/**
 * How much to refund if the guest cancels at $now (matches Airbnb Moderate + non-refundable rate).
 * Returns [cents, reason].
 */
function refund_due(array $b, ?DateTimeImmutable $now = null): array
{
    $now = $now ?? new DateTimeImmutable('now');
    $paid = (int) $b['amount_cents'];
    $nights = max(1, (int) $b['nights']);
    $perNight = $paid / $nights;
    $checkin = new DateTimeImmutable($b['arrive'] . ' 15:00');
    $bookedAt = (new DateTimeImmutable('@' . (int) ($b['paid_at'] ?: $b['created_at'])))->setTimezone($now->getTimezone());
    if ($now->getTimestamp() - $bookedAt->getTimestamp() <= 86400 && $checkin->getTimestamp() - $bookedAt->getTimestamp() >= 7 * 86400) {
        return [$paid, 'Within 24 hours of booking: full refund.'];
    }
    if (($b['rate_type'] ?? 'flexible') === 'nonref') return [0, 'Non-refundable rate: no refund.'];
    if ($now <= $checkin->modify('-5 days')) return [$paid, 'More than 5 days before check-in: full refund.'];
    if ($now < $checkin) {
        return [(int) round(0.5 * ($nights - 1) * $perNight), 'Less than 5 days before check-in: first night kept, 50% of the other nights refunded.'];
    }
    $stayed = (int) floor(($now->getTimestamp() - $checkin->getTimestamp()) / 86400) + 1;
    $left = max(0, $nights - $stayed - 1);
    return [(int) round(0.5 * $left * $perNight), 'After check-in: nights stayed plus one more kept, 50% of the rest refunded.'];
}

/* ---------- Changing dates (from /admin/) ---------- */

/** Move a paid booking to new dates. Returns [error or null, price difference in cents (new price - paid)]. */
function change_booking_dates(string $ref, string $arrive, string $depart): array
{
    $b = get_booking($ref);
    if (!$b || $b['status'] !== 'paid') return ['Booking not found.', 0];
    $err = validate_stay($arrive, $depart, null, true, $ref);
    if ($err) return [$err, 0];
    $q = quote($arrive, $depart, (bool) $b['pets'], (string) $b['rate_type']);
    $note = trim((string) $b['notes'] . "\n" . date('j M Y') . ': dates changed from ' . $b['arrive'] . '→' . $b['depart'] . ' to ' . $arrive . '→' . $depart);
    db()->prepare('UPDATE bookings SET arrive=?, depart=?, nights=?, lockbox_sent_at=NULL, thanks_sent_at=NULL, notes=? WHERE ref=?')
        ->execute([$arrive, $depart, $q['nights'], $note, $ref]);
    send_dates_changed_email(get_booking($ref));
    return [null, $q['total_cents'] - (int) $b['amount_cents']];
}
