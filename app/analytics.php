<?php
// Cookieless visit counting and the Monday summary email.
// No cookies and no IP addresses are stored: a visitor is a daily-changing scrambled code.

function visitor_id(): string
{
    $day = date('Y-m-d');
    $salt = kv_get('salt');
    if (!$salt || strpos($salt, $day . ':') !== 0) { $salt = $day . ':' . bin2hex(random_bytes(16)); kv_set('salt', $salt); }
    return substr(hash('sha256', $salt . ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16);
}

function is_bot(): bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python|headless|lighthouse|monitor|uptime/i', $ua);
}

function device_type(): string
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/iPad|Tablet/i', $ua)) return 'tablet';
    return preg_match('/Mobi|Android|iPhone/i', $ua) ? 'phone' : 'desktop';
}

function traffic_source(): string
{
    $q = $_GET['utm_source'] ?? '';
    if ($q) return strtolower(substr(preg_replace('/[^a-z0-9._-]/i', '', $q), 0, 30));
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = strtolower((string) parse_url($ref, PHP_URL_HOST));
    $own = strtolower((string) parse_url((string) cfg('base_url'), PHP_URL_HOST));
    if (!$host || $host === $own || $host === 'www.' . $own || 'www.' . $host === $own || $host === ($_SERVER['HTTP_HOST'] ?? '')) return 'direct';
    $map = ['google' => 'Google', 'bing' => 'Bing', 'duckduckgo' => 'DuckDuckGo', 'instagram' => 'Instagram',
        'facebook' => 'Facebook', 'fb.' => 'Facebook', 'whatsapp' => 'WhatsApp', 'airbnb' => 'Airbnb',
        'booking.com' => 'Booking.com', 'tiktok' => 'TikTok', 'pinterest' => 'Pinterest', 't.co' => 'X', 'twitter' => 'X'];
    foreach ($map as $needle => $name) if (strpos($host, $needle) !== false) return $name;
    return preg_replace('/^www\./', '', $host);
}

function record_hit(string $path): void
{
    if (is_bot() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || PHP_SAPI === 'cli') return;
    try {
        db()->prepare('INSERT INTO hits(ts,day,path,device,source,visitor) VALUES(?,?,?,?,?,?)')
            ->execute([time(), date('Y-m-d'), $path, device_type(), traffic_source(), visitor_id()]);
    } catch (Throwable $e) { /* never break a page for stats */ }
}

function record_event(string $name): void
{
    if (is_bot() && PHP_SAPI !== 'cli') return;
    try {
        db()->prepare('INSERT INTO events(ts,day,name,visitor) VALUES(?,?,?,?)')
            ->execute([time(), date('Y-m-d'), $name, PHP_SAPI === 'cli' ? 'cli' : visitor_id()]);
    } catch (Throwable $e) {}
}

/** Numbers for a period [from, to) of Y-m-d days. */
function stats_for(string $from, string $to): array
{
    $q = function (string $sql) use ($from, $to) { $s = db()->prepare($sql); $s->execute([$from, $to]); return $s; };
    $visitors = (int) $q('SELECT COUNT(DISTINCT day||visitor) FROM hits WHERE day>=? AND day<?')->fetchColumn();
    $views = (int) $q('SELECT COUNT(*) FROM hits WHERE day>=? AND day<?')->fetchColumn();
    $devices = $q('SELECT device, COUNT(DISTINCT day||visitor) n FROM hits WHERE day>=? AND day<? GROUP BY device ORDER BY n DESC')->fetchAll(PDO::FETCH_KEY_PAIR);
    $sources = $q('SELECT source, COUNT(DISTINCT day||visitor) n FROM hits WHERE day>=? AND day<? GROUP BY source ORDER BY n DESC LIMIT 8')->fetchAll(PDO::FETCH_KEY_PAIR);
    $pages = $q('SELECT path, COUNT(*) n FROM hits WHERE day>=? AND day<? GROUP BY path ORDER BY n DESC LIMIT 6')->fetchAll(PDO::FETCH_KEY_PAIR);
    $bookView = (int) $q("SELECT COUNT(DISTINCT day||visitor) FROM hits WHERE day>=? AND day<? AND path='/book/'")->fetchColumn();
    $ev = $q('SELECT name, COUNT(DISTINCT day||visitor) n FROM events WHERE day>=? AND day<? GROUP BY name')->fetchAll(PDO::FETCH_KEY_PAIR);
    $fromTs = strtotime($from); $toTs = strtotime($to);
    $s = db()->prepare("SELECT COUNT(*) c, COALESCE(SUM(amount_cents),0) s FROM bookings WHERE created_at>=? AND created_at<?");
    $s->execute([$fromTs, $toTs]); $started = $s->fetch();
    $s = db()->prepare("SELECT COUNT(*) c, COALESCE(SUM(amount_cents),0) s FROM bookings WHERE status='paid' AND paid_at>=? AND paid_at<?");
    $s->execute([$fromTs, $toTs]); $paid = $s->fetch();
    return [
        'visitors' => $visitors, 'views' => $views, 'devices' => $devices, 'sources' => $sources, 'pages' => $pages,
        'funnel' => [
            'Saw the Book page' => $bookView,
            'Picked dates' => (int) ($ev['dates_chosen'] ?? 0),
            'Went to payment' => (int) $started['c'],
            'Paid' => (int) $paid['c'],
        ],
        'paid_cents' => (int) $paid['s'],
    ];
}

function pct_change(int $now, int $before): string
{
    if ($before === 0) return $now ? 'new' : 'no change';
    $p = (int) round(($now - $before) / $before * 100);
    return ($p > 0 ? '+' : '') . $p . '% on the week before';
}

function weekly_summary(): array
{
    $end = today(); // up to and including yesterday
    $from = $end->modify('-7 day'); $prev = $from->modify('-7 day');
    $now = stats_for($from->format('Y-m-d'), $end->format('Y-m-d'));
    $before = stats_for($prev->format('Y-m-d'), $from->format('Y-m-d'));

    $sec = fn(string $t) => '<h2 style="font-family:Georgia,serif;font-weight:400;font-size:18px;margin:22px 0 6px">' . h($t) . '</h2>';
    $list = function (array $kv, int $total) {
        if (!$kv) return '<p style="color:#5E5F5C">None yet.</p>';
        $s = '<table style="width:100%;border-collapse:collapse;font-size:14px">';
        foreach ($kv as $k => $v) {
            $p = $total ? ' (' . round($v / $total * 100) . '%)' : '';
            $s .= '<tr><td style="padding:5px 0;border-bottom:1px solid #EEE">' . h($k) . '</td><td style="padding:5px 0;border-bottom:1px solid #EEE;text-align:right">' . $v . $p . '</td></tr>';
        }
        return $s . '</table>';
    };

    $html = '<p style="font-size:15px">' . h($from->format('D j M')) . ' to ' . h($end->modify('-1 day')->format('D j M Y')) . '</p>';
    $html .= '<p style="font-size:28px;font-family:Georgia,serif;margin:6px 0">' . $now['visitors'] . ' visitors</p>'
        . '<p style="color:#5E5F5C;margin:0">' . h(pct_change($now['visitors'], $before['visitors'])) . ' · ' . $now['views'] . ' page views</p>';
    $html .= $sec('Bookings') . '<p>' . $now['funnel']['Paid'] . ' paid (' . euro($now['paid_cents']) . ') from ' . $now['funnel']['Went to payment'] . ' started.</p>';
    $html .= $sec('Where people dropped off') . $list($now['funnel'], 0);
    $f = array_values($now['funnel']); $labels = array_keys($now['funnel']); $worst = null; $worstLoss = 0;
    for ($i = 0; $i < 3; $i++) { $loss = $f[$i] - $f[$i + 1]; if ($loss > $worstLoss) { $worstLoss = $loss; $worst = "Most people stopped between “{$labels[$i]}” and “{$labels[$i + 1]}”."; } }
    if ($worst) $html .= '<p style="font-size:13px;color:#5E5F5C">' . h($worst) . '</p>';
    $html .= $sec('Phone or computer') . $list($now['devices'], $now['visitors']);
    $html .= $sec('Where visitors came from') . $list($now['sources'], $now['visitors']);
    $names = ['/' => 'Home', '/the-cabin/' => 'The Cabin', '/things-to-do-ballina/' => 'The Area', '/book/' => 'Book', '/contact/' => 'Contact & FAQ'];
    $pages = []; foreach ($now['pages'] as $p => $n) $pages[$names[$p] ?? $p] = $n;
    $html .= $sec('Top pages') . $list($pages, 0);

    // Upcoming stays, next 14 days
    $until = $end->modify('+14 day')->format('Y-m-d');
    $stays = [];
    $s = db()->prepare("SELECT * FROM bookings WHERE status='paid' AND arrive>=? AND arrive<? ORDER BY arrive");
    $s->execute([$end->format('Y-m-d'), $until]);
    foreach ($s->fetchAll() as $b) $stays[] = [$b['arrive'], nice_date($b['arrive'], false) . ' to ' . nice_date($b['depart'], false) . ': ' . $b['name'] . ' (direct, ' . $b['guests'] . ' guest' . ($b['guests'] > 1 ? 's' : '') . ($b['pets'] ? ', pets' : '') . ')'];
    foreach (ical_events(true) as [$src, $a, $d]) {
        if ($a >= $end->format('Y-m-d') && $a < $until) $stays[] = [$a, nice_date($a, false) . ' to ' . nice_date($d, false) . ": $src"];
    }
    usort($stays, fn($x, $y) => strcmp($x[0], $y[0]));
    $html .= $sec('Upcoming stays, next 14 days');
    $html .= $stays ? '<ul style="padding-left:18px;font-size:14px">' . implode('', array_map(fn($x) => '<li>' . h($x[1]) . '</li>', $stays)) . '</ul>' : '<p style="color:#5E5F5C">No arrivals in the next 14 days.</p>';
    $html .= '<p style="font-size:12px;color:#5E5F5C;margin-top:20px">Visitors are counted without cookies. Search phrases and Google rankings are in Google Search Console.</p>';

    $title = 'Your week on anbothandubh.ie';
    $full = email_layout($title, $html);
    return ['subject' => 'Bothán Dubh weekly: ' . $now['visitors'] . ' visitors, ' . $now['funnel']['Paid'] . ' bookings', 'html' => $full, 'text' => email_text($title, $html)];
}
