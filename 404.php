<?php
require __DIR__ . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
http_response_code(404);
$p = ['path' => '/404', 'title' => 'Page not found | An Bothán Dubh', 'description' => 'Page not found.', 'noindex' => true];
page_head($p);
?>
<div class="wrap" style="padding-block:40px 80px"><h1>Page not found</h1><p>Sorry, that page doesn't exist. Try the <a href="/">home page</a> or <a href="/book/">check dates</a>.</p></div>
<?php page_foot($p);
