<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
$p = [
    'path' => '/contact/',
    'title' => 'Contact & FAQ | An Bothán Dubh Cabin, Attymass, Ballina',
    'description' => 'Questions about An Bothán Dubh? Message us on WhatsApp. FAQ on pets, check-in, solar power, Wi-Fi, parking, cancellation and getting to Attymass near Ballina.',
    'image' => '/photos/37.webp',
    'jsonld' => [json_decode('{"@context": "https://schema.org", "@type": "FAQPage", "mainEntity": [{"@type": "Question", "name": "Are pets allowed?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. The pet fee is €10 per night, however many pets you bring. Dogs must stay off the bed. Our sheepdog Sadie and our cat live with us at our house and may wander over."}}, {"@type": "Question", "name": "What time is check-in and check-out?", "acceptedAnswer": {"@type": "Answer", "text": "Check-in is 3pm to 9pm, with self check-in using a lockbox. Please let us know your arrival time. Check-out is by 11am."}}, {"@type": "Question", "name": "Is there Wi-Fi or TV?", "acceptedAnswer": {"@type": "Answer", "text": "No, that\'s part of the off-grid experience. Phone reception is good, so you can use your phone as a hotspot if you need to work."}}, {"@type": "Question", "name": "Can I charge my phone and laptop?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. There\'s one socket, good for up to 500 watts, which is plenty for phones, laptops and cameras. Please don\'t plug in high-power items like hair dryers or kettles."}}, {"@type": "Question", "name": "How is the cabin heated? Can I cook?", "acceptedAnswer": {"@type": "Answer", "text": "A gas stove keeps the cabin warm, and there\'s a gas hob and oven, fridge with freezer box, a kettle for the hob and cooking basics."}}, {"@type": "Question", "name": "Is there parking?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, free parking for one car, with a gravel path to the cabin. There are three low steps up to the deck."}}, {"@type": "Question", "name": "What\'s the cancellation policy?", "acceptedAnswer": {"@type": "Answer", "text": "You choose your rate when you book. Flexible: full refund up to 5 days before check-in; after that the first night is non-refundable and 50% of the other nights is refunded. Non-refundable: 10% cheaper, no refund. Both rates: free cancellation within 24 hours of booking if you booked at least 7 days before check-in."}}, {"@type": "Question", "name": "How far is it from Ballina and Knock Airport?", "acceptedAnswer": {"@type": "Answer", "text": "Ballina is 7 km away, about a 10-minute drive. Ireland West Airport Knock is 35 km away."}}, {"@type": "Question", "name": "How do I pay, and is it secure?", "acceptedAnswer": {"@type": "Answer", "text": "You pay in full by card when you book. Payment is handled by Stripe, so we never see your card details. You get a confirmation email straight away."}}, {"@type": "Question", "name": "When do I get the lockbox code?", "acceptedAnswer": {"@type": "Answer", "text": "We email the lockbox code and directions 2 days before you arrive."}}]}', true)],
];
page_head($p);
?>
<div class="wrap">
<div class="pagehead"><h1>Contact &amp; FAQ</h1><p>Questions before you book? Message us on WhatsApp, we usually reply the same day.</p></div>
    <div class="contactgrid">
      <div class="contactcard">
        <a class="wa-big" href="<?= WA_URL ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></svg>Message us on WhatsApp</a>
        <p class="small" style="margin-top:12px">WhatsApp: 086 346 8725</p>
        <h3>Finding us</h3>
        <p>Attymass, Ballina, Co. Mayo. Exact directions and the lockbox code are sent before you arrive.</p>
        <a class="cta ghost" href="https://www.google.com/maps/search/Attymass,+Ballina,+Co.+Mayo" target="_blank" rel="noopener">Open in Google Maps</a>
      </div>
      <div class="faq">
        <h2>Frequently asked questions</h2>
        <details open><summary>Are pets allowed?</summary><p>Yes. The pet fee is €10 per night, however many pets you bring. Dogs must stay off the bed. Our sheepdog Sadie and our cat live with us at our house and may wander over.</p></details>
        <details><summary>What time is check-in and check-out?</summary><p>Check-in is 3pm to 9pm, with self check-in using a lockbox. Please let us know your arrival time. Check-out is by 11am.</p></details>
        <details><summary>Is there Wi-Fi or TV?</summary><p>No, that's part of the off-grid experience. Phone reception is good, so you can use your phone as a hotspot if you need to work.</p></details>
        <details><summary>Can I charge my phone and laptop?</summary><p>Yes. There's one socket, good for up to 500 watts, which is plenty for phones, laptops and cameras. Please don't plug in high-power items like hair dryers or kettles.</p></details>
        <details><summary>How is the cabin heated? Can I cook?</summary><p>A gas stove keeps the cabin warm, and there's a gas hob and oven, fridge with freezer box, a kettle for the hob and cooking basics.</p></details>
        <details><summary>Is there parking?</summary><p>Yes, free parking for one car, with a gravel path to the cabin. There are three low steps up to the deck.</p></details>
        <details><summary>What's the cancellation policy?</summary><p>You choose your rate when you book. Flexible: full refund up to 5 days before check-in; after that the first night is non-refundable and 50% of the other nights is refunded. Non-refundable: 10% cheaper, no refund. Both rates: free cancellation within 24 hours of booking if you booked at least 7 days before check-in.</p></details>
        <details><summary>How far is it from Ballina and Knock Airport?</summary><p>Ballina is 7 km away, about a 10-minute drive. Ireland West Airport Knock is 35 km away.</p></details>
        <details><summary>How do I pay, and is it secure?</summary><p>You pay in full by card when you book. Payment is handled by Stripe, so we never see your card details. You get a confirmation email straight away.</p></details>
        <details><summary>When do I get the lockbox code?</summary><p>We email the lockbox code and directions 2 days before you arrive.</p></details>
      </div>
    </div>
</div>
<?php page_foot($p);
