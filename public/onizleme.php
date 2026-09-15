<?php
// Önizleme girişi: 30 günlük çerez koyar ve ana sayfaya yönlendirir. Yayına alınınca dosya kalabilir; etkisiz olur.
setcookie('onizleme', '1', ['expires' => time() + 30 * 86400, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
header('Location: /?onizleme=1', true, 302);
exit;
