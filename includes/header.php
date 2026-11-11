<?php
/**
 * YoncaHastanesi — Ortak üst bölüm
 * Sayfa başlığı, meta bilgiler, header, navigasyon ve flash mesajları.
 */

if (!function_exists('oturum_hazirla')) {
    require_once __DIR__ . '/../functions.php';
}

oturum_hazirla();

$sayfa_baslik = $sayfa_baslik ?? 'Yonca Hastanesi — Hastane Randevu Yönetimi';
$meta_aciklama = $meta_aciklama ?? 'Hastane poliklinikleri için hızlı, güvenli ve mobil uyumlu randevu yönetim sistemi.';
$aktif_sayfa = $aktif_sayfa ?? '';
$flashlar = flash_mesajlari();

$flashBasliklari = [
    'basari' => 'İşlem tamamlandı',
    'hata'   => 'Bir sorun oluştu',
    'bilgi'  => 'Bilgilendirme',
];

$flashIkonlari = [
    'basari' => 'check-circle',
    'hata'   => 'alert',
    'bilgi'  => 'info',
];
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <meta name="description" content="<?= e($meta_aciklama) ?>">
    <meta name="author" content="Yonca Hastanesi">
    <title><?= e($sayfa_baslik) ?></title>

    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
</head>
<body>
<a class="skip-link" href="#main">İçeriğe geç</a>

<div class="bg-decor" aria-hidden="true">
    <span class="orb orb--1"></span>
    <span class="orb orb--2"></span>
    <span class="orb orb--3"></span>
</div>

<?php require __DIR__ . '/icons.php'; ?>

<header class="site-header" data-header>
    <div class="header-inner">
        <a class="brand" href="index.php" aria-label="Yonca Hastanesi ana sayfa">
            <span class="brand-mark">
                <svg class="icon" aria-hidden="true"><use href="#icon-medical-cross"></use></svg>
            </span>
            <span class="brand-text">
                <strong>Yonca<span>Hastanesi</span></strong>
                <small>hastane randevu sistemi</small>
            </span>
        </a>

        <nav class="main-nav" id="mainNav" aria-label="Ana menü">
            <a href="index.php" class="<?= $aktif_sayfa === 'anasayfa' ? 'is-active' : '' ?>" <?= $aktif_sayfa === 'anasayfa' ? 'aria-current="page"' : '' ?>>Genel bakış</a>
            <a href="index.php#randevular">Randevular</a>
            <a href="index.php#hizmetler">Poliklinikler</a>
            <a href="index.php#ozellikler">Neden biz?</a>
            <a href="ekle.php" class="<?= $aktif_sayfa === 'yeni' ? 'is-active' : '' ?>" <?= $aktif_sayfa === 'yeni' ? 'aria-current="page"' : '' ?>>Yeni randevu</a>
        </nav>
        <button id="tema-degistir" class="btn btn--soft btn--sm" aria-label="Temayı değiştir">
    <svg class="icon icon-moon" aria-hidden="true"><use href="#icon-moon"></use></svg>
    <svg class="icon icon-sun" aria-hidden="true" style="display:none;"><use href="#icon-sun"></use></svg>
    <span class="tema-text">Koyu Tema</span>
</button>
        <div class="header-actions">
            <a class="btn btn--primary btn--sm header-cta" href="ekle.php">
                <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                Randevu oluştur
            </a>

            <button type="button" class="nav-toggle" data-menu-degistir aria-expanded="false"
                    aria-controls="mainNav" aria-label="Menüyü aç">
                <svg class="icon nav-toggle__open" aria-hidden="true"><use href="#icon-menu"></use></svg>
                <svg class="icon nav-toggle__close" aria-hidden="true"><use href="#icon-close"></use></svg>
            </button>
        </div>
    </div>
</header>

<?php if ($flashlar): ?>
    <div class="toast-stack" aria-live="polite" aria-atomic="false">
        <?php foreach ($flashlar as $flash):
            $tip = (string) ($flash['tip'] ?? 'bilgi');
            if (!isset($flashBasliklari[$tip])) {
                $tip = 'bilgi';
            }
            ?>
            <div class="toast toast--<?= e($tip) ?>" data-toast role="status">
                <span class="toast__icon">
                    <svg class="icon" aria-hidden="true"><use href="#icon-<?= e($flashIkonlari[$tip]) ?>"></use></svg>
                </span>
                <div class="toast__body">
                    <strong><?= e($flashBasliklari[$tip]) ?></strong>
                    <p><?= e($flash['mesaj'] ?? '') ?></p>
                </div>
                <button type="button" class="toast__close" data-toast-kapat aria-label="Bildirimi kapat">
                    <svg class="icon" aria-hidden="true"><use href="#icon-close"></use></svg>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main">
