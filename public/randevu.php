<?php
// Sizi Arayalım formu — e-posta ile bildirim.
// Hostingde PHP mail() kapalı; gönderim doğrudan MX teslimiyle yapılır (bkz. smtp.php).
// Alıcı info@dtaycan.com.tr → Cloudflare Email Routing → dt.mehmettas@hotmail.com
require __DIR__ . '/smtp.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }
if (!empty($_POST['site'])) { echo '{"ok":true}'; exit; } // bot tuzağı
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
      . "IP       : " . ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '') . "\n";
$sent = dtaycan_smtp_send($to, $subject, $body, 'randevu@dtaycan.com.tr', 'dtaycan.com.tr randevu');

// yedek: sunucuda log tut (mail gitmese de kayıt kalsın) — docroot dışı
@file_put_contents(__DIR__ . '/../randevu-talepleri.log', date('c') . "\t$ad\t$tel\t$konu\t$zaman\t" . str_replace(["\r","\n"], ' ', $not) . "\t" . ($sent ? 'mail-ok' : 'mail-fail') . "\n", FILE_APPEND);

echo json_encode(['ok' => true, 'mail' => (bool)$sent]);
