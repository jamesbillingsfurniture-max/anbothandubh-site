<?php
/*
 * An Bothán Dubh: private settings.
 *
 * Copy this file to config.php (same folder) ON THE HOSTING ONLY and fill it in
 * using cPanel > File Manager > Edit. Never commit config.php, never paste keys in chat.
 */
return [
    // Website address, no trailing slash.
    'base_url' => 'https://anbothandubh.ie',

    // 'test' = Stripe sandbox (sk_test_ keys) and a TEST banner on every page.
    // 'live' = real payments. Only switch when James says go.
    'mode' => 'test',

    // Demo mode: no Stripe at all, "payment" is simulated. Only for the preview copy.
    'demo' => false,

    // Stripe (Dashboard > Developers > API keys, and Webhooks for the signing secret).
    'stripe_secret_key'     => '',   // sk_test_... (later sk_live_...)
    'stripe_webhook_secret' => '',   // whsec_...

    // Calendars to import (private links, paste them here only).
    'ical_import' => [
        'Airbnb'      => '',
        'Booking.com' => '',
    ],
    // Secret word for the calendar this site exports to Airbnb / Booking.com.
    // Make it long and random, e.g. 30 letters and numbers.
    'ical_export_key' => '',

    // Email
    'owner_email' => '',   // where booking alerts + Monday summary go (set in config.php)
    'from_email'  => 'bookings@anbothandubh.ie',         // must exist in cPanel > Email Accounts
    'from_name'   => 'An Bothán Dubh',
    // 'send' = really send, 'log' = save emails as files in data/outbox (for testing)
    'mail_mode'   => 'log',
    // Optional fallback if hosting mail is unreliable: Resend API key (free tier).
    'resend_api_key' => '',

    // Arrival details sent to the guest 2 days before arrival.
    'lockbox_code' => '',
    'eircode'      => '',   // the guest is told to follow this; the cabin is just before/past it
    'directions'   => '',   // optional extra directions, added under the Eircode

    // Admin page (/admin/) password. Make it long.
    'admin_password' => '',

    // Fáilte Ireland short-term letting registration number (shown in the footer).
    'str_number' => '',

    // Booking rules (normally leave as they are)
    'rate_weekday'     => 130,  // Mon-Thu nights
    'rate_weekend'     => 140,  // Fri, Sat, Sun nights
    'pet_fee'          => 10,   // per night, however many pets
    'min_nights'       => 2,
    'max_nights'       => 28,
    'months_ahead'     => 12,
    'min_days_notice'  => 1,    // 1 = earliest arrival is tomorrow
];
