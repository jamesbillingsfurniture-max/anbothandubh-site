<?php
// Import Airbnb / Booking.com calendars, and export direct bookings for them to import.

const ICAL_CACHE_SECONDS = 600; // re-fetch at most every 10 minutes

function ical_cache_file(string $name): string
{
    return DATA_DIR . '/ical-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)) . '.ics';
}

function ical_fetch(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'BothanDubh-calendar-sync/1.0',
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($body === false || $code !== 200 || strpos($body, 'BEGIN:VCALENDAR') === false) return null;
    return $body;
}

/** Refresh cached calendars if stale. Returns [name => ok?]. Keeps the last good copy on failure. */
function ical_refresh(bool $force = false): array
{
    $status = [];
    foreach ((array) cfg('ical_import') as $name => $url) {
        if (!$url) continue;
        $file = ical_cache_file($name);
        if (!$force && is_file($file) && filemtime($file) > time() - ICAL_CACHE_SECONDS) { $status[$name] = true; continue; }
        $body = ical_fetch($url);
        if ($body !== null) {
            if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
            file_put_contents($file . '.tmp', $body); rename($file . '.tmp', $file);
            $status[$name] = true;
        } else {
            $status[$name] = false;
            if (is_file($file)) touch($file, time() - ICAL_CACHE_SECONDS + 120); // retry in ~2 min
        }
    }
    return $status;
}

/** Parse VEVENTs into [ [start Y-m-d, end Y-m-d (exclusive), summary], ... ] */
function ical_parse(string $ics): array
{
    $ics = preg_replace("/\r?\n[ \t]/", '', $ics); // unfold lines
    $events = [];
    if (!preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ics, $m)) return [];
    foreach ($m[1] as $ev) {
        $start = $end = null; $summary = '';
        foreach (preg_split('/\r?\n/', $ev) as $line) {
            if (preg_match('/^DTSTART[^:]*:(\d{8})/', $line, $x)) $start = $x[1];
            elseif (preg_match('/^DTEND[^:]*:(\d{8})/', $line, $x)) $end = $x[1];
            elseif (preg_match('/^SUMMARY[^:]*:(.*)$/', $line, $x)) $summary = trim($x[1]);
        }
        if (!$start) continue;
        $s = DateTimeImmutable::createFromFormat('!Ymd', $start);
        $e = $end ? DateTimeImmutable::createFromFormat('!Ymd', $end) : $s->modify('+1 day');
        if ($e <= $s) $e = $s->modify('+1 day');
        $events[] = [$s->format('Y-m-d'), $e->format('Y-m-d'), $summary];
    }
    return $events;
}

/** Imported stays: [ [source, start, end, summary], ... ] from cached files. */
function ical_events(bool $refresh = false): array
{
    if ($refresh) ical_refresh();
    $out = [];
    foreach ((array) cfg('ical_import') as $name => $url) {
        $file = ical_cache_file($name);
        if (!$url || !is_file($file)) continue;
        foreach (ical_parse((string) file_get_contents($file)) as $e) $out[] = [$name, $e[0], $e[1], $e[2]];
    }
    // Demo copy: invent a few blocked stays so the calendar looks real.
    if (is_demo() && !$out) {
        $t = today();
        foreach ([[6, 3], [15, 4], [29, 3], [44, 5]] as [$s, $n]) {
            $out[] = ['Airbnb', $t->modify("+$s day")->format('Y-m-d'), $t->modify('+' . ($s + $n) . ' day')->format('Y-m-d'), 'Reserved'];
        }
    }
    return $out;
}

function ical_blocked_nights(bool $refresh = false): array
{
    $out = [];
    foreach (ical_events($refresh) as $e) {
        for ($x = new DateTimeImmutable($e[1]); $x < new DateTimeImmutable($e[2]); $x = $x->modify('+1 day')) {
            $out[$x->format('Y-m-d')] = true;
        }
    }
    return $out;
}

/** Calendar of direct bookings, for Airbnb and Booking.com to import. */
function ical_export(): string
{
    expire_holds();
    $rows = db()->query("SELECT ref, arrive, depart, status FROM bookings WHERE status IN ('paid','hold') AND depart >= date('now','-1 day') ORDER BY arrive")->fetchAll();
    $host = parse_url((string) cfg('base_url'), PHP_URL_HOST) ?: 'anbothandubh.ie';
    $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Bothan Dubh//Direct bookings//EN', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH'];
    $stamp = gmdate('Ymd\THis\Z');
    foreach ($rows as $r) {
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:' . $r['ref'] . '@' . $host;
        $lines[] = 'DTSTAMP:' . $stamp;
        $lines[] = 'DTSTART;VALUE=DATE:' . str_replace('-', '', $r['arrive']);
        $lines[] = 'DTEND;VALUE=DATE:' . str_replace('-', '', $r['depart']);
        $lines[] = 'SUMMARY:Booked direct (' . $r['ref'] . ')';
        $lines[] = 'END:VEVENT';
    }
    $lines[] = 'END:VCALENDAR';
    return implode("\r\n", $lines) . "\r\n";
}
