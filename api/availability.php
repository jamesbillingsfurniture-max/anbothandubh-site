<?php
// Booked nights + prices for the booking calendar.
require dirname(__DIR__) . '/app/bootstrap.php';
$blocked = array_keys(blocked_nights(true));
sort($blocked);
json_out([
    'blocked' => array_values(array_filter($blocked, fn($d) => $d >= today()->format('Y-m-d'))),
    'first' => first_arrival()->format('Y-m-d'),
    'last' => last_departure()->format('Y-m-d'),
    'rate_weekday' => base_rate('weekday'),
    'rate_weekend' => base_rate('weekend'),
    'specials' => array_map(function ($r) {
        return ['s' => $r['start'], 'e' => $r['end_excl'], 'p' => (int) $r['price'], 'o' => (bool) $r['offer'], 'l' => (string) $r['label'], 'm' => $r['min_nights'] ? (int) $r['min_nights'] : null];
    }, array_values(array_filter(special_rates(), function ($r) { return $r['end_excl'] >= date('Y-m-d'); }))),
    'nonref_pct' => nonref_pct(),
    'today' => today()->format('Y-m-d'),
    'disc' => ['lm_pct' => disc('lm_pct'), 'lm_days' => disc('lm_days'), 'eb_pct' => disc('eb_pct'),
        'eb_from' => today()->modify('+' . disc('eb_months') . ' month')->format('Y-m-d'), 'eb_months' => disc('eb_months'),
        'week_pct' => disc('week_pct'), 'week_nights' => max(2, disc('week_nights')), 'max_pct' => disc('max_pct')],
    'pet_fee' => (int) cfg('pet_fee'),
    'min_nights' => (int) cfg('min_nights'),
    'max_nights' => (int) cfg('max_nights'),
    'demo' => is_demo(),
    'test' => !is_live(),
]);
