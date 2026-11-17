<?php
// Run Mondays at 8am by cPanel Cron Jobs:  php /home/CPANELUSER/PATH/cron/weekly-summary.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$s = weekly_summary();
$ok = send_mail((string) cfg('owner_email'), $s['subject'], $s['html'], $s['text']);
echo date('c') . ' weekly summary ' . ($ok ? 'sent' : 'FAILED') . "\n";
