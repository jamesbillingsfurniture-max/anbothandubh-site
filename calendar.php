<?php
// Direct bookings as a calendar feed. Give this link to Airbnb and Booking.com ("import calendar"):
//   https://anbothandubh.ie/calendar.php?key=YOUR_ical_export_key
require __DIR__ . '/app/bootstrap.php';
$key = (string) cfg('ical_export_key');
if (strlen($key) < 16 || !hash_equals($key, (string) ($_GET['key'] ?? ''))) { http_response_code(404); exit('Not found'); }
header('Content-Type: text/calendar; charset=utf-8');
header('Cache-Control: no-store');
echo ical_export();
