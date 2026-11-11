<?php
/**
 * Yonca Hastanesi — Kayıt silme işlemi (yalnızca POST)
 */
require_once __DIR__ . '/functions.php';
oturum_hazirla();

$donus = guvenli_donus($_POST['donus'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_ekle('hata', 'Silme işlemi yalnızca form üzerinden yapılabilir.');
    yonlendir($donus);
}

if (!csrf_dogrula($_POST['csrf'] ?? null)) {
    flash_ekle('hata', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
    yonlendir($donus);
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    flash_ekle('hata', 'Silinecek randevu bulunamadı.');
    yonlendir($donus);
}

if (randevu_sil($id)) {
    flash_ekle('basari', 'Randevu kalıcı olarak silindi.');
} else {
    flash_ekle('hata', 'Randevu bulunamadı veya daha önce silinmiş.');
}

yonlendir($donus . '#randevular');
