<?php
// Tiny beacon for the booking funnel ("picked dates"). No cookies.
require dirname(__DIR__) . '/app/bootstrap.php';
$name = $_GET['e'] ?? '';
if (in_array($name, ['dates_chosen', 'details_step'], true)) record_event($name);
http_response_code(204);
