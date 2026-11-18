<?php
// Run every hour by cPanel Cron Jobs:  php /home/CPANELUSER/PATH/cron/hourly.php
// Refreshes Airbnb/Booking.com calendars, tidies unpaid holds, sends lockbox emails due (2 days before arrival).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$cal = ical_refresh(true);
expire_holds();
$sent = send_due_lockbox_emails();
$thanks = send_due_thankyou_emails();

// Tell James once a day if a calendar keeps failing to download.
$bad = array_keys(array_filter($cal, fn($ok) => !$ok));
if ($bad && kv_get('ical_alert_day') !== date('Y-m-d')) {
    kv_set('ical_alert_day', date('Y-m-d'));
    $msg = '<p>The website could not download the ' . h(implode(' and ', $bad)) . ' calendar. Booked dates from there may be out of date on the website until it works again. If this keeps happening, the calendar link may have changed.</p>';
    send_mail((string) cfg('owner_email'), 'An Bothán Dubh website: calendar sync problem', email_layout('Calendar sync problem', $msg), email_text('Calendar sync problem', $msg));
}
// Keep stats for 2 years only.
db()->prepare('DELETE FROM hits WHERE ts < ?')->execute([strtotime('-2 years')]);
echo date('c') . " calendars: " . json_encode($cal) . " lockbox emails sent: $sent, thank-you emails sent: $thanks\n";
