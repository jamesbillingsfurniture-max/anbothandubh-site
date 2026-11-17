<?php
// Page header and footer shared by all 5 pages.

const WA_SVG = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></svg>';
const WA_URL = 'https://wa.me/353863468725';

function asset(string $path): string
{
    $file = SITE_DIR . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function page_head(array $p): void
{
    // $p: path, title, description, image (optional), jsonld (optional array of arrays), noindex
    $base = rtrim((string) cfg('base_url'), '/');
    $canonical = $base . $p['path'];
    $img = $base . ($p['image'] ?? '/photos/15.webp');
    if (empty($p['noindex'])) record_hit($p['path']);
    header('Content-Type: text/html; charset=utf-8');
    $nav = [['/', 'Home'], ['/the-cabin/', 'The Cabin'], ['/things-to-do-ballina/', 'The Area'], ['/contact/', 'Contact &amp; FAQ']];
    ?><!doctype html>
<html lang="en-IE">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($p['title']) ?></title>
<meta name="description" content="<?= h($p['description']) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">
<?php if (!empty($p['noindex']) || !is_live()): ?><meta name="robots" content="noindex"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="Bothán Dubh">
<meta property="og:title" content="<?= h($p['title']) ?>">
<meta property="og:description" content="<?= h($p['description']) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:image" content="<?= h($img) ?>">
<meta property="og:locale" content="en_IE">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#1E2023">
<link rel="icon" href="/assets/icon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Young+Serif&family=Instrument+Sans:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= asset('/assets/style.css') ?>">
<?php foreach ($p['jsonld'] ?? [] as $ld): ?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endforeach; ?>
</head>
<body>
<a class="skip" href="#content">Skip to content</a>
<?php if (!is_live()): ?>
<div class="banner"><span class="long"><?= is_demo() ? 'PREVIEW COPY: no real bookings or payments. Booked dates are examples.' : 'TEST MODE: Stripe sandbox. Use card 4242 4242 4242 4242. No real money is taken.' ?></span><span class="short"><?= is_demo() ? 'Preview: no real bookings' : 'Test mode: no real payments' ?></span></div>
<?php endif; ?>
<div class="wrap">
  <header class="top">
    <a href="/" class="brand" style="color:inherit;text-decoration:none">Bothán Dubh<small>Off-grid cabin · Ballina · Co. Mayo</small></a>
    <nav id="nav" aria-label="Main"><?php foreach ($nav as [$href, $label]): ?><a href="<?= $href ?>"<?= $href === $p['path'] ? ' aria-current="page"' : '' ?>><?= $label ?></a><?php endforeach; ?><a href="/book/" class="navbook"<?= str_starts_with($p['path'], '/book/') ? ' aria-current="page"' : '' ?>>Book direct</a></nav>
  </header>
</div>
<main id="content">
<?php
}

function page_foot(array $p, array $scripts = []): void
{
    $base = rtrim((string) cfg('base_url'), '/');
    $cur = fn(string $href) => $href === $p['path'] || ($href === '/book/' && str_starts_with($p['path'], '/book/')) ? ' aria-current="page"' : '';
    ?>
</main>
<footer><div class="wrap">
  <div class="social"><span class="tag">#AnBothanDubh</span><span>Staying with us? Tag your photos so we can share them.</span><div class="share" id="share" data-site="<?= h($base) ?>"><b>Share:</b></div></div>
  <div class="social"><b style="color:var(--ink)">Follow us</b><a href="https://www.instagram.com/anbothandubh" target="_blank" rel="noopener">Instagram @anbothandubh</a><a href="https://www.facebook.com/anbothandubh" target="_blank" rel="noopener">Facebook @anbothandubh</a></div>
  <div class="social"><span>Bothán Dubh · off-grid cabin, Attymass, Ballina, Co. Mayo</span><span>Book direct with the host · card payments by Stripe</span><span class="small">Fáilte Ireland short-term letting registration no.: <?= cfg('str_number') ? h(cfg('str_number')) : 'to follow' ?></span></div>
</div></footer>

<nav class="tabbar" id="tabbar" aria-label="Main"><a href="/"<?= $cur('/') ?>><svg viewBox="0 0 24 24"><path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>Home</a><a href="/the-cabin/"<?= $cur('/the-cabin/') ?>><svg viewBox="0 0 24 24"><path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/></svg>Cabin</a><a href="/things-to-do-ballina/"<?= $cur('/things-to-do-ballina/') ?>><svg viewBox="0 0 24 24"><path d="M3 20 9 8l4 7 3-4 5 9z"/><circle cx="17" cy="6" r="2"/></svg>Area</a><a href="/contact/" class="tb-wa"<?= $cur('/contact/') ?>><?= WA_SVG ?>Contact</a><a href="/book/" class="tb-book"<?= $cur('/book/') ?>><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>Book</a></nav>
<a class="wa-float" href="<?= WA_URL ?>" target="_blank" rel="noopener" aria-label="Message us on WhatsApp"><?= WA_SVG ?></a>
<script src="<?= asset('/assets/site.js') ?>" defer></script>
<?php foreach ($scripts as $s): ?><script src="<?= asset($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
<?php
}

/** Structured data describing the cabin, used on the home and cabin pages. */
function lodging_jsonld(): array
{
    $base = rtrim((string) cfg('base_url'), '/');
    $amen = fn(string $n, bool $v = true) => ['@type' => 'LocationFeatureSpecification', 'name' => $n, 'value' => $v];
    return [
        '@context' => 'https://schema.org',
        '@type' => 'VacationRental',
        '@id' => $base . '/#cabin',
        'name' => 'Bothán Dubh',
        'description' => 'A solar-powered, off-grid cabin for two in Attymass, 7 km from Ballina, Co. Mayo. Dark skies, walks from the door, pets welcome.',
        'url' => $base . '/',
        'image' => [$base . '/photos/15.webp', $base . '/photos/37.webp', $base . '/photos/00.webp', $base . '/photos/29.webp', $base . '/photos/35.webp', $base . '/photos/39.webp', $base . '/photos/07.webp', $base . '/photos/38.webp'],
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Attymass, Ballina', 'addressRegion' => 'Co. Mayo', 'addressCountry' => 'IE'],
        'telephone' => '+353863468725',
        'priceRange' => '€130–€140 per night',
        'petsAllowed' => true,
        'checkinTime' => '15:00', 'checkoutTime' => '11:00',
        'numberOfRooms' => 1,
        'occupancy' => ['@type' => 'QuantitativeValue', 'maxValue' => 2],
        'floorSize' => ['@type' => 'QuantitativeValue', 'value' => 19, 'unitCode' => 'MTK'],
        'amenityFeature' => [
            $amen('Off-grid solar power'), $amen('Hot shower'), $amen('Kitchen with gas hob and oven'), $amen('Gas stove heating'),
            $amen('Private deck and garden'), $amen('Free parking'), $amen('Pets allowed'), $amen('Self check-in'), $amen('Wi-Fi', false), $amen('TV', false),
        ],
        'sameAs' => ['https://www.instagram.com/anbothandubh', 'https://www.facebook.com/anbothandubh'],
    ];
}
