<?php
/*
 * Sitenin ayarlarını sunucuda saklar (adrese ?ayar eklenince açılan panel: src/scripts/ayar-panel.ts).
 * GET  : kayıtlı ayarları JSON olarak verir; kayıt yoksa {} (site varsayılanlarla açılır).
 * POST : {"sifre": "..."} şifreyi denetler; {"sifre": "...", "ayar": {...}} ayarları kaydeder.
 * Kayıt, yayın klasörünün dışında, kullanıcının ana klasöründe durur: yeni yayın onu silmez.
 * Canlı site ile deneme sitesinin klasörleri ayrı olduğu için kayıtları da ayrıdır.
 * Şifrenin kendisi burada yazılı değil, yalnızca özeti (password_hash) var.
 */
const SIFRE_OZETI = '$2y$11$Pxb0U7qPn2fqY8D4EDdLVufq55QEGnWG/6B6PANC8Ve8EykZEeyzu';
const EN_FAZLA = 150;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex');

function cevap($kod, $veri)
{
    http_response_code($kod);
    echo json_encode($veri, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ayarların saklandığı klasör: önce ana klasör, olmazsa yayın klasörünün bir üstü, en son yayın klasöründe kapalı bir klasör */
function klasor()
{
    $adaylar = [];
    $ev = getenv('HOME');
    if ($ev) $adaylar[] = rtrim($ev, '/') . '/.muratvural-ayar';
    if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
        $k = @posix_getpwuid(posix_geteuid());
        if (!empty($k['dir'])) $adaylar[] = rtrim($k['dir'], '/') . '/.muratvural-ayar';
    }
    $adaylar[] = dirname(__DIR__) . '/.muratvural-ayar';
    $adaylar[] = __DIR__ . '/.ayar-kayit';
    foreach ($adaylar as $d) {
        if ((is_dir($d) || @mkdir($d, 0700, true)) && is_writable($d)) {
            if (strpos($d, __DIR__) === 0 && !file_exists("$d/.htaccess")) @file_put_contents("$d/.htaccess", "Require all denied\nDeny from all\n");
            return $d;
        }
    }
    return null;
}

function dosya()
{
    $d = klasor();
    return $d ? $d . '/ayar-' . substr(md5(__DIR__), 0, 12) . '.json' : null;
}

/* yalnızca beklenen biçimdeki değerler: bölüm → ad → evet/hayır, 0–150 arası sayı ya da kısa bir kelime */
function temizle($a)
{
    if (!is_array($a)) return null;
    $out = [];
    foreach ($a as $bolum => $alanlar) {
        if (!is_string($bolum) || !preg_match('/^[a-z_]{1,24}$/', $bolum) || !is_array($alanlar) || count($out) >= 12) continue;
        foreach ($alanlar as $ad => $v) {
            if (!is_string($ad) || !preg_match('/^[a-z_]{1,24}$/', $ad) || count($out[$bolum] ?? []) >= 24) continue;
            if (is_bool($v)) $out[$bolum][$ad] = $v;
            elseif (is_int($v) || is_float($v)) $out[$bolum][$ad] = max(0, min(EN_FAZLA, round($v, 2)));
            elseif (is_string($v) && preg_match('/^[a-z_]{1,16}$/', $v)) $out[$bolum][$ad] = $v;
        }
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['durum'])) {
        $f = dosya();
        cevap(200, ['php' => PHP_VERSION, 'kayit_yeri_var' => $f !== null, 'ana_klasorde' => $f !== null && strpos($f, __DIR__) !== 0, 'kayit_var' => $f !== null && is_file($f)]);
    }
    $f = dosya();
    $s = $f && is_file($f) ? json_decode((string) @file_get_contents($f), true) : null;
    echo json_encode(is_array($s) ? (object) $s : new stdClass(), JSON_UNESCAPED_UNICODE); // boş kayıt da {} olarak gider
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') cevap(405, ['hata' => 'yontem']);

$girdi = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($girdi) || !is_string($girdi['sifre'] ?? null) || !password_verify($girdi['sifre'], SIFRE_OZETI)) {
    sleep(1); // tahminle denemeyi yavaşlatır
    cevap(403, ['hata' => 'sifre']);
}
if (!array_key_exists('ayar', $girdi)) cevap(200, ['tamam' => true]);

$ayar = temizle($girdi['ayar']);
if ($ayar === null) cevap(400, ['hata' => 'ayar']);
$f = dosya();
if (!$f) cevap(500, ['hata' => 'kayit_yeri']);

$json = json_encode((object) $ayar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if (is_file($f)) @copy($f, $f . '.onceki'); // bir önceki kayıt da saklanır
$gecici = $f . '.' . getmypid() . '.tmp';
if (@file_put_contents($gecici, $json, LOCK_EX) === false || !@rename($gecici, $f)) {
    @unlink($gecici);
    cevap(500, ['hata' => 'yazilamadi']);
}
cevap(200, ['tamam' => true, 'ayar' => (object) $ayar]);
