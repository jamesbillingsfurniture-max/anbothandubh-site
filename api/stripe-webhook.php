<?php
// Stripe calls this after a payment. Set it up in Stripe: Developers > Webhooks > Add endpoint:
//   https://anbothandubh.ie/api/stripe-webhook.php
//   events: checkout.session.completed, checkout.session.expired
require dirname(__DIR__) . '/app/bootstrap.php';

$payload = (string) file_get_contents('php://input');
$event = stripe_verify_webhook($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '', (string) cfg('stripe_webhook_secret'));
if (!$event) json_out(['error' => 'Invalid signature'], 400);

$obj = $event['data']['object'] ?? [];
$ref = (string) ($obj['client_reference_id'] ?? ($obj['metadata']['ref'] ?? ''));

switch ($event['type'] ?? '') {
    case 'checkout.session.completed':
    case 'checkout.session.async_payment_succeeded':
        if ($ref && ($obj['payment_status'] ?? '') === 'paid') {
            $b = get_booking($ref);
            if ($b && $b['status'] !== 'paid') record_event('paid');
            mark_paid($ref, (string) ($obj['id'] ?? ''), (string) ($obj['payment_intent'] ?? ''));
        }
        break;
    case 'checkout.session.expired':
        if ($ref) release_hold($ref);
        break;
}
json_out(['received' => true]);
