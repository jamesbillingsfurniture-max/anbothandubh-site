<?php
// Sending email: hosting mail (PHP mail), Resend as a fallback, or 'log' to save to files for testing.

function send_mail(string $to, string $subject, string $html, string $text, ?string $replyTo = null): bool
{
    $fromEmail = (string) cfg('from_email');
    $fromName = (string) cfg('from_name');
    $replyTo ??= (string) cfg('owner_email');

    if (cfg('mail_mode') !== 'send') {
        $dir = DATA_DIR . '/outbox';
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        $name = date('Ymd-His') . '-' . substr(md5($to . $subject . microtime()), 0, 6) . '.html';
        $meta = '<!-- To: ' . h($to) . ' | Reply-To: ' . h($replyTo) . ' | Subject: ' . h($subject) . " -->\n";
        file_put_contents("$dir/$name", $meta . $html);
        return true;
    }

    if (cfg('resend_api_key')) {
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . cfg('resend_api_key'), 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'from' => "$fromName <$fromEmail>", 'to' => [$to], 'reply_to' => $replyTo,
                'subject' => $subject, 'html' => $html, 'text' => $text,
            ]),
        ]);
        curl_exec($ch);
        $ok = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) < 300;
        curl_close($ch);
        if ($ok) return true;
    }

    $boundary = 'b' . bin2hex(random_bytes(12));
    $headers = [
        'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . " <$fromEmail>",
        "Reply-To: $replyTo",
        'MIME-Version: 1.0',
        "Content-Type: multipart/alternative; boundary=\"$boundary\"",
    ];
    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text))
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html)) . "--$boundary--\r\n";
    return mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, implode("\r\n", $headers), '-f' . $fromEmail);
}

/** Wrap content in the simple branded email layout. $rows = [[label, value], ...] */
function email_layout(string $title, string $intro, array $rows = [], string $after = ''): string
{
    $t = '';
    foreach ($rows as [$k, $v]) {
        $t .= '<tr><td style="padding:7px 0;border-bottom:1px solid #DDD9D2;color:#5E5F5C;width:40%;vertical-align:top">' . h($k)
            . '</td><td style="padding:7px 0;border-bottom:1px solid #DDD9D2;font-weight:600">' . nl2br(h($v)) . '</td></tr>';
    }
    return '<!doctype html><html><body style="margin:0;background:#F2F0EC;font-family:Helvetica,Arial,sans-serif;color:#1E2023;line-height:1.55">'
        . '<div style="max-width:560px;margin:0 auto;padding:24px 16px">'
        . '<div style="font-family:Georgia,serif;font-size:22px;margin-bottom:16px">Bothán Dubh</div>'
        . '<div style="background:#fff;border:1px solid #DDD9D2;border-radius:12px;padding:22px">'
        . '<h1 style="font-family:Georgia,serif;font-weight:400;font-size:24px;margin:0 0 12px">' . h($title) . '</h1>'
        . $intro
        . ($t ? '<table style="width:100%;border-collapse:collapse;font-size:15px;margin:14px 0">' . $t . '</table>' : '')
        . $after . '</div>'
        . '<p style="font-size:12px;color:#5E5F5C;margin-top:16px">Bothán Dubh · off-grid cabin · Attymass, Ballina, Co. Mayo · WhatsApp 086 346 8725</p>'
        . '</div></body></html>';
}

/** Plain-text version from the same rows. */
function email_text(string $title, string $intro, array $rows = [], string $after = ''): string
{
    $s = "$title\n\n" . trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>'], ["\n\n", "\n"], $intro)))) . "\n\n";
    foreach ($rows as [$k, $v]) $s .= "$k: $v\n";
    if ($after) $s .= "\n" . trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>'], ["\n\n", "\n", "\n"], $after)))) . "\n";
    return $s . "\nBothán Dubh, Attymass, Ballina, Co. Mayo. WhatsApp 086 346 8725\n";
}
