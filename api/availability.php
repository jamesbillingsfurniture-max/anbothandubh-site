<?php
// Booked nights + prices for the booking calendar.
require dirname(__DIR__) . '/app/bootstrap.php';
$blocked = array_keys(blocked_nights(true));
sort($blocked);
json_out([
    'blocked' => array_values(array_filter($blocked, fn($d) => $d >= today()->format('Y-m-d'))),
    'first' => first_arrival()->format('Y-m-d'),
    'last' => last_departure()->format('Y-m-d'),
    'rate_weekday' => (int) cfg('rate_weekday'),
    'rate_weekend' => (int) cfg('rate_weekend'),
    'pet_fee' => (int) cfg('pet_fee'),
    'min_nights' => (int) cfg('min_nights'),
    'max_nights' => (int) cfg('max_nights'),
    'demo' => is_demo(),
    'test' => !is_live(),
]);
