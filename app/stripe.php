<?php
// Stripe Checkout using the plain web API (no libraries to install).

function stripe_request(string $method, string $path, array $params = []): array
{
    $key = (string) cfg('stripe_secret_key');
    if (!$key) throw new RuntimeException('Stripe key not set');
    if (cfg('mode') !== 'live' && strpos($key, 'sk_test_') !== 0) throw new RuntimeException('Test mode needs a sk_test_ key');
    $url = 'https://api.stripe.com/v1/' . $path;
    $ch = curl_init();
    $opts = [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
        CURLOPT_USERPWD => $key . ':', CURLOPT_HTTPHEADER => ['Stripe-Version: 2024-06-20'],
    ];
    if ($method === 'POST') { $opts[CURLOPT_POST] = true; $opts[CURLOPT_POSTFIELDS] = http_build_query($params); }
    elseif ($params) { $url .= '?' . http_build_query($params); }
    $opts[CURLOPT_URL] = $url;
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $data = json_decode((string) $body, true) ?: [];
    if ($code >= 300) throw new RuntimeException('Stripe error: ' . ($data['error']['message'] ?? "HTTP $code"));
    return $data;
}

function stripe_checkout_session(array $b): array
{
    $base = rtrim((string) cfg('base_url'), '/');
    $desc = $b['nights'] . ' nights, ' . nice_date($b['arrive'], false) . ' to ' . nice_date($b['depart']) . ', '
        . $b['guests'] . ' guest' . ($b['guests'] > 1 ? 's' : '') . ($b['pets'] ? ' + pets' : '') . (($b['rate_type'] ?? '') === 'nonref' ? ', non-refundable rate' : ', flexible rate');
    return stripe_request('POST', 'checkout/sessions', [
        'mode' => 'payment',
        'locale' => 'en',
        'customer_email' => $b['email'],
        'client_reference_id' => $b['ref'],
        'metadata' => ['ref' => $b['ref']],
        'payment_intent_data' => ['description' => "An Bothán Dubh {$b['ref']}: $desc", 'metadata' => ['ref' => $b['ref']]],
        'line_items' => [[
            'quantity' => 1,
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => (int) $b['amount_cents'],
                'product_data' => ['name' => 'An Bothán Dubh: ' . $desc],
            ],
        ]],
        'expires_at' => time() + 31 * 60,
        'success_url' => $base . '/book/thanks/?ref=' . $b['ref'] . '&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $base . '/book/?cancelled=' . $b['ref'],
    ]);
}

/** Check a Stripe webhook signature. Returns the event array or null. */
function stripe_verify_webhook(string $payload, string $sigHeader, string $secret, int $tolerance = 300): ?array
{
    if (!$secret || !$sigHeader) return null;
    $t = null; $sigs = [];
    foreach (explode(',', $sigHeader) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k === 't') $t = (int) $v;
        if ($k === 'v1') $sigs[] = $v;
    }
    if (!$t || !$sigs || abs(time() - $t) > $tolerance) return null;
    $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
    foreach ($sigs as $s) {
        if (hash_equals($expected, $s)) return json_decode($payload, true);
    }
    return null;
}
