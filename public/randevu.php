<?php
// Sizi Arayalım formu — e-posta ile bildirim.
// Hostingde PHP mail() kapalı; gönderim doğrudan MX teslimiyle yapılır (bkz. smtp.php).
// Alıcı info@dtaycan.com.tr → Cloudflare Email Routing → dt.mehmettas@hotmail.com
require __DIR__ . '/smtp.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }
if (!empty($_POST['site'])) { echo '{"ok":true}'; exit; } // bot tuzağı

$logFile = __DIR__ . '/../randevu-talepleri.log';
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

// Oran sınırlama (denetim raporu 27.09.2026, P1 "randevu formu: rate limit"): aynı IP 20 sn
// içinde ikinci kez gönderemez. Harici servis/CAPTCHA eklemeden, log dosyasının son birkaç
// satırından okunuyor — satır formatı date\tad\ttel\tkonu\tzaman\tnot\tmail\tip (8. alan).
if ($ip !== '' && is_file($logFile)) {
    $lines = @file($logFile, FILE_IGNORE_NEW_LINES) ?: [];
    foreach (array_reverse(array_slice($lines, -20)) as $line) {
        $parts = explode("\t", $line);
        if (($parts[7] ?? '') === $ip) {
            $ts = strtotime($parts[0] ?? '');
            if ($ts && time() - $ts < 20) { http_response_code(429); echo '{"ok":false,"err":"rate"}'; exit; }
            break;
        }
    }
}

$ad = trim(strip_tags($_POST['ad'] ?? ''));
$tel = trim(strip_tags($_POST['telefon'] ?? ''));
$konu = trim(strip_tags($_POST['konu'] ?? ''));
$zaman = trim(strip_tags($_POST['zaman'] ?? ''));
$not = trim(strip_tags($_POST['not'] ?? ''));
if ($ad === '' || strlen(preg_replace('/\D/', '', $tel)) < 10) { echo '{"ok":false}'; exit; }

$to = 'info@dtaycan.com.tr';
$subject = 'Randevu talebi: ' . $ad;
$body = "dtaycan.com.tr — Sizi Arayalım formu\n\n"
      . "Ad Soyad : $ad\n"
      . "Telefon  : $tel\n"
      . "Konu     : $konu\n"
      . "Zaman    : $zaman\n"
      . "Not      : $not\n\n"
      . "Tarih    : " . date('d.m.Y H:i') . "\n"
      . "IP       : $ip\n";
$sent = dtaycan_smtp_send($to, $subject, $body, 'randevu@dtaycan.com.tr', 'dtaycan.com.tr randevu');

// Log: mail gitmese de kayıt kalsın (yedek) — docroot dışı. IP 8. alan olarak eklendi (oran
// sınırlama bunu okuyor). KVKK aydınlatma metni "en geç 1 yıl içinde silinir" diyor — bu vaadi
// koddan uygulamak için her yazımda 1 yıldan eski satırlar süzülüp dosya yeniden yazılıyor
// (denetim raporu 27.09.2026, "randevu formu: ... saklama süresi").
$newLine = date('c') . "\t$ad\t$tel\t$konu\t$zaman\t" . str_replace(["\r", "\n"], ' ', $not) . "\t" . ($sent ? 'mail-ok' : 'mail-fail') . "\t$ip\n";
$cutoff = time() - 366 * 86400;
$kept = [];
foreach (@file($logFile, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
    $ts = strtotime(explode("\t", $line, 2)[0] ?? '');
    if ($ts && $ts >= $cutoff) $kept[] = $line;
}
$kept[] = rtrim($newLine, "\n");
@file_put_contents($logFile, implode("\n", $kept) . "\n", LOCK_EX);

echo json_encode(['ok' => true, 'mail' => (bool)$sent]);
