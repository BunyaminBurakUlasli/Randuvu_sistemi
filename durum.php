<?php
/**
 * YoncaHastanesi — Randevu durumu güncelleme (onayla / tamamla / iptal et)
 */
require_once __DIR__ . '/functions.php';
oturum_hazirla();

$donus = guvenli_donus($_POST['donus'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_ekle('hata', 'Durum güncellemesi yalnızca form üzerinden yapılabilir.');
    yonlendir($donus);
}

if (!csrf_dogrula($_POST['csrf'] ?? null)) {
    flash_ekle('hata', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
    yonlendir($donus);
}

$id = (int) ($_POST['id'] ?? 0);
$yeniDurum = (string) ($_POST['durum'] ?? '');

if ($id <= 0 || !isset(durumlar()[$yeniDurum])) {
    flash_ekle('hata', 'Geçersiz randevu veya durum bilgisi.');
    yonlendir($donus);
}

if (randevu_durum_guncelle($id, $yeniDurum)) {
    $mesajlar = [
        'bekliyor'   => 'Randevu yeniden “Bekliyor” durumuna alındı.',
        'onaylandi'  => 'Randevu onaylandı. Hastaya bilgi verebilirsiniz.',
        'tamamlandi' => 'Randevu tamamlandı olarak işaretlendi.',
        'iptal'      => 'Randevu iptal edildi.',
    ];
    flash_ekle('basari', $mesajlar[$yeniDurum] ?? 'Randevu durumu güncellendi.');
} else {
    flash_ekle('hata', 'Randevu bulunamadı veya durum güncellenemedi.');
}

yonlendir($donus . '#randevular');
