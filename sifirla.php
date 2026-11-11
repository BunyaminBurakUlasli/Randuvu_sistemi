<?php
/**
 * Yonca Hastanesi — Demo verilerini yeniden yükleme (yalnızca POST)
 */
require_once __DIR__ . '/functions.php';
oturum_hazirla();

$donus = guvenli_donus($_POST['donus'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_ekle('hata', 'Sıfırlama işlemi yalnızca form üzerinden yapılabilir.');
    yonlendir($donus);
}

if (!csrf_dogrula($_POST['csrf'] ?? null)) {
    flash_ekle('hata', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
    yonlendir($donus);
}

unset($_SESSION['randevular'], $_SESSION['son_id']);
oturum_hazirla();

flash_ekle('bilgi', 'Demo verileri yeniden yüklendi. Örnek randevular listeye eklendi.');
yonlendir('index.php#randevular');
