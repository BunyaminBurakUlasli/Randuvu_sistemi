<?php
/**
 * YoncaHastanesi — Ana sayfa / Genel bakış
 */
require_once __DIR__ . '/functions.php';
oturum_hazirla();

$filtreler = mevcut_filtreler();
$tumRandevular = randevulari_getir();
$istatistik = istatistikler($tumRandevular);
$randevular = filtrele_ve_sirala($tumRandevular, $filtreler);
$bugunRandevular = bugunun_randevulari($tumRandevular);
$takvim = takvim_verisi($tumRandevular, $filtreler['takvim'] ?: null, $filtreler['tarih'] ?: null);
$hizmetListesi = hizmetler();
$durumListesi = durumlar();
$sorgu = sorgu_olustur($filtreler);
$donus = 'index.php' . ($sorgu !== '' ? '?' . $sorgu : '');

$sonraki = $istatistik['sonraki'];
$sonrakiHizmet = $sonraki ? hizmet_bilgisi((string) $sonraki['hizmet']) : null;
$sonrakiPersonel = $sonraki ? personel_bilgisi((string) $sonraki['personel']) : null;

$donutParcalari = [];
$donutAci = 0.0;
$donutToplam = max(1, (int) $istatistik['toplam']);
foreach ($durumListesi as $anahtar => $durum) {
    $adet = (int) ($istatistik['durumlar'][$anahtar] ?? 0);
    if ($adet <= 0) {
        continue;
    }
    $pay = ($adet / $donutToplam) * 100;
    $donutParcalari[] = $durum['dot'] . ' ' . round($donutAci, 2) . '% ' . round($donutAci + $pay, 2) . '%';
    $donutAci += $pay;
}
$donutGradyan = $donutParcalari
    ? 'conic-gradient(' . implode(', ', $donutParcalari) . ')'
    : 'conic-gradient(var(--surface-3) 0% 100%)';

$aktifFiltreler = [];
if ($filtreler['q'] !== '') {
    $aktifFiltreler[] = ['anahtar' => 'q', 'etiket' => 'Arama: ' . $filtreler['q']];
}
if ($filtreler['hizmet'] !== '') {
    $aktifFiltreler[] = ['anahtar' => 'hizmet', 'etiket' => 'Poliklinik: ' . hizmet_bilgisi($filtreler['hizmet'])['ad']];
}
if ($filtreler['durum'] !== '') {
    $aktifFiltreler[] = ['anahtar' => 'durum', 'etiket' => 'Durum: ' . durum_bilgisi($filtreler['durum'])['ad']];
}
if ($filtreler['tarih'] !== '') {
    $aktifFiltreler[] = ['anahtar' => 'tarih', 'etiket' => 'Tarih: ' . turkce_tarih($filtreler['tarih'])];
}

$sayfa_baslik = 'Yonca Hastanesi — Hastane Randevu Yönetim Sistemi';
$meta_aciklama = 'Yonca Hastanesi ile poliklinikleri, doktorları ve günlük muayene programını tek bir düzenli panelde yönetin.';
$aktif_sayfa = 'anasayfa';
require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="shell">
        <div class="hero-grid">
            <div class="hero-copy reveal">
                <span class="eyebrow">
                    <svg class="icon" aria-hidden="true"><use href="#icon-stethoscope"></use></svg>
                    Hastane poliklinikleri için
                </span>
                <h1>Hastane randevularınızı <em>tek ekranda</em> yönetin.</h1>
                <p class="lead">Poliklinikler, doktorlar ve günlük muayene programı tek panelde. Yonca Hastanesi ile hasta akışını sakin ve düzenli yönetin.</p>

                <div class="hero-actions">
                    <a class="btn btn--primary" href="ekle.php">
                        <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                        Yeni randevu oluştur
                    </a>
                    <a class="btn btn--ghost" href="#randevular">
                        <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                        Programı görüntüle
                    </a>
                </div>

                <div class="hero-trust">
                    <span><svg class="icon" aria-hidden="true"><use href="#icon-check-circle"></use></svg> Hasta odaklı</span>
                    <span><svg class="icon" aria-hidden="true"><use href="#icon-shield"></use></svg> Güvenli oturum</span>
                    <span><svg class="icon" aria-hidden="true"><use href="#icon-zap"></use></svg> Anında özet</span>
                </div>
            </div>

            <div class="hero-visual reveal reveal--d1">
                <!-- Tahmini Gelir Çipini üste (top) alıp satır içi CSS ile büyütüyoruz -->
                <div class="float-chip float-chip--top" style="font-size: 1.15rem; padding: 12px 24px; z-index: 10;">
                    <svg class="icon" aria-hidden="true" style="width: 24px; height: 24px;"><use href="#icon-wallet"></use></svg>
                    <strong><?= e(para_formatla($istatistik['ciro'])) ?></strong> tahmini gelir
                </div>

                <div class="agenda-card">
                    <div class="agenda-head">
                        <div>
                            <span class="agenda-kicker">Bugünün muayene programı</span>
                            <strong><?= e(turkce_tarih(date('Y-m-d'), true)) ?></strong>
                        </div>
                        <span class="agenda-live">Canlı</span>
                    </div>

                    <div class="agenda-list">
                        <?php if ($bugunRandevular): ?>
                            <?php foreach (array_slice($bugunRandevular, 0, 3) as $randevu):
                                $hizmet = hizmet_bilgisi((string) $randevu['hizmet']);
                                $durum = durum_bilgisi((string) $randevu['durum']);
                                ?>
                                <div class="agenda-item">
                                    <span class="agenda-time"><?= e($randevu['saat']) ?></span>
                                    <span class="avatar avatar--sm tone-<?= e(avatar_rengi((string) $randevu['ad_soyad'])) ?>">
                                        <?= e(bas_harfler((string) $randevu['ad_soyad'])) ?>
                                    </span>
                                    <span class="agenda-info">
                                        <strong><?= e($randevu['ad_soyad']) ?></strong>
                                        <small><?= e($hizmet['kisa']) ?></small>
                                    </span>
                                    <span class="agenda-dot <?= e('tone-' . $durum['renk']) ?>" title="<?= e($durum['ad']) ?>"></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="agenda-empty">
                                <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                                <span>Bugün için henüz randevu yok.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="agenda-foot">
                        <div class="agenda-foot__top">
                            <span>Günlük doluluk</span>
                            <strong>%<?= (int) $istatistik['doluluk'] ?></strong>
                        </div>
                        <div class="progress"><span style="--progress: <?= (int) $istatistik['doluluk'] ?>%"></span></div>
                        <div class="agenda-foot__sub">
                            <span><?= (int) $istatistik['bugun'] ?> / 12 randevu</span>
                            <a href="ekle.php">Randevu ekle <svg class="icon" aria-hidden="true"><use href="#icon-arrow-right"></use></svg></a>
                        </div>
                    </div>
                </div>

                <!-- Takvim Çipini alta (bottom) alıyoruz -->
                <div class="float-chip float-chip--bottom">
                    <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                    <strong><?= (int) $istatistik['bugun'] ?></strong> randevu bugün
                </div>
            </div>
        </div>
    </div>
</section>

<!-- İSTATİSTİK KARTLARI -->
<section class="stats-section" aria-label="Randevu özeti">
    <div class="shell">
        <div class="stats-grid">
            <article class="stat-card tone-violet reveal">
                <div class="stat-top">
                    <span class="stat-label">Toplam randevu</span>
                    <span class="stat-icon"><svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg></span>
                </div>
                <strong class="stat-value"><?= (int) $istatistik['toplam'] ?></strong>
                <span class="stat-meta">Tüm kayıtlar <span class="stat-pill">arşiv</span></span>
            </article>

            <article class="stat-card tone-rose reveal reveal--d1">
                <div class="stat-top">
                    <span class="stat-label">Bugünkü program</span>
                    <span class="stat-icon"><svg class="icon" aria-hidden="true"><use href="#icon-clock"></use></svg></span>
                </div>
                <strong class="stat-value"><?= (int) $istatistik['bugun'] ?></strong>
                <span class="stat-meta"><?= (int) $istatistik['bugun_onayli'] ?> onaylı randevu <span class="stat-pill">bugün</span></span>
            </article>

            <article class="stat-card tone-amber reveal reveal--d2">
                <div class="stat-top">
                    <span class="stat-label">Onay bekleyen</span>
                    <span class="stat-icon"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg></span>
                </div>
                <strong class="stat-value"><?= (int) $istatistik['bekleyen'] ?></strong>
                <span class="stat-meta">İlginizi bekliyor <span class="stat-pill">aksiyon</span></span>
            </article>

            <article class="stat-card tone-teal reveal reveal--d3">
                <div class="stat-top">
                    <span class="stat-label">Tahmini gelir</span>
                    <span class="stat-icon"><svg class="icon" aria-hidden="true"><use href="#icon-wallet"></use></svg></span>
                </div>
                <strong class="stat-value"><?= e(para_formatla($istatistik['ciro'])) ?></strong>
                <span class="stat-meta">İptaller hariç <span class="stat-pill">güncel</span></span>
            </article>
        </div>
    </div>
</section>

<!-- RANDEVU PANELİ -->
<section class="section dashboard" id="randevular">
    <div class="shell">
        <div class="section-head">
            <div class="section-head__text">
                <span class="kicker">Hasta akışı</span>
                <h2>Gününüzü <span class="grad-text">planlayın</span></h2>
                <p>Hasta adı, poliklinik ve duruma göre arayın; kayıtları tek dokunuşla yönetin. Tüm veriler oturumunuz boyunca güvenle saklanır.</p>
            </div>
            <div class="section-head__actions">
                <a class="btn btn--soft btn--sm" href="ekle.php">
                    <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                    Yeni randevu
                </a>
            </div>
        </div>

        <form class="filter-bar" method="get" action="index.php" role="search">
            <div class="filter-search">
                <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                <label class="sr-only" for="q">Randevu ara</label>
                <input type="search" id="q" name="q" value="<?= e($filtreler['q']) ?>" placeholder="Hasta adı, telefon veya poliklinik ara...">
            </div>

            <label class="sr-only" for="filtreHizmet">Polikliniğe göre filtrele</label>
            <select name="hizmet" id="filtreHizmet" data-auto-submit>
                <option value="">Tüm poliklinikler</option>
                <?php foreach ($hizmetListesi as $anahtar => $hizmet): ?>
                    <option value="<?= e($anahtar) ?>" <?= $filtreler['hizmet'] === $anahtar ? 'selected' : '' ?>><?= e($hizmet['kisa']) ?></option>
                <?php endforeach; ?>
            </select>

            <label class="sr-only" for="filtreDurum">Duruma göre filtrele</label>
            <select name="durum" id="filtreDurum" data-auto-submit>
                <option value="">Tüm durumlar</option>
                <?php foreach ($durumListesi as $anahtar => $durum): ?>
                    <option value="<?= e($anahtar) ?>" <?= $filtreler['durum'] === $anahtar ? 'selected' : '' ?>><?= e($durum['ad']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="filter-date">
                <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                <label class="sr-only" for="filtreTarih">Tarihe göre filtrele</label>
                <input type="date" id="filtreTarih" name="tarih" value="<?= e($filtreler['tarih']) ?>" data-auto-submit>
            </div>

            <input type="hidden" name="sirala" value="<?= e($filtreler['sirala']) ?>">
            <input type="hidden" name="takvim" value="<?= e($filtreler['takvim']) ?>">

            <button class="btn btn--primary filter-submit" type="submit">
                <svg class="icon" aria-hidden="true"><use href="#icon-filter"></use></svg>
                Filtrele
            </button>

            <?php if (filtre_aktif_mi($filtreler)): ?>
                <a class="filter-clear" href="index.php">
                    <svg class="icon" aria-hidden="true"><use href="#icon-close"></use></svg>
                    Temizle
                </a>
            <?php endif; ?>
        </form>

        <?php if ($aktifFiltreler): ?>
            <div class="active-filters" aria-label="Aktif filtreler">
                <span class="active-filters__label">Aktif:</span>
                <?php foreach ($aktifFiltreler as $filtre): ?>
                    <a class="filter-chip" href="<?= e(filtre_url([], [$filtre['anahtar']])) ?>">
                        <?= e($filtre['etiket']) ?>
                        <svg class="icon" aria-hidden="true"><use href="#icon-close"></use></svg>
                    </a>
                <?php endforeach; ?>
                <a class="active-filters__clear" href="index.php">Tümünü temizle</a>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <section class="panel panel--list" aria-labelledby="listeBaslik">
                <div class="list-head">
                    <h3 class="list-title" id="listeBaslik">
                        <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                        Randevu listesi
                        <span class="count"><?= count($randevular) ?></span>
                    </h3>

                    <div class="list-tools">
                        <form class="sort-form" method="get" action="index.php">
                            <input type="hidden" name="q" value="<?= e($filtreler['q']) ?>">
                            <input type="hidden" name="hizmet" value="<?= e($filtreler['hizmet']) ?>">
                            <input type="hidden" name="durum" value="<?= e($filtreler['durum']) ?>">
                            <input type="hidden" name="tarih" value="<?= e($filtreler['tarih']) ?>">
                            <input type="hidden" name="takvim" value="<?= e($filtreler['takvim']) ?>">
                            <label class="sr-only" for="sirala">Sıralama</label>
                            <select name="sirala" id="sirala" data-auto-submit>
                                <option value="akilli" <?= $filtreler['sirala'] === 'akilli' ? 'selected' : '' ?>>Akıllı sıralama</option>
                                <option value="tarih-artan" <?= $filtreler['sirala'] === 'tarih-artan' ? 'selected' : '' ?>>Tarih (eski → yeni)</option>
                                <option value="tarih-azalan" <?= $filtreler['sirala'] === 'tarih-azalan' ? 'selected' : '' ?>>Tarih (yeni → eski)</option>
                                <option value="isim" <?= $filtreler['sirala'] === 'isim' ? 'selected' : '' ?>>İsim (A → Z)</option>
                                <option value="yeni" <?= $filtreler['sirala'] === 'yeni' ? 'selected' : '' ?>>Son eklenenler</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="status-tabs" aria-label="Duruma göre hızlı filtre">
                    <a class="status-tab <?= $filtreler['durum'] === '' ? 'is-active' : '' ?>" href="<?= e(filtre_url([], ['durum'])) ?>">
                        Tümü <b><?= (int) $istatistik['toplam'] ?></b>
                    </a>
                    <?php foreach ($durumListesi as $anahtar => $durum): ?>
                        <a class="status-tab <?= $filtreler['durum'] === $anahtar ? 'is-active' : '' ?>" href="<?= e(filtre_url(['durum' => $anahtar])) ?>">
                            <span class="status-dot" style="--dot: <?= e($durum['dot']) ?>"></span>
                            <?= e($durum['ad']) ?>
                            <b><?= (int) ($istatistik['durumlar'][$anahtar] ?? 0) ?></b>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (!$randevular): ?>
                    <div class="empty-state">
                        <div class="empty-orbit">
                            <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                        </div>
                        <h3>Bu filtreye uygun randevu bulunamadı</h3>
                        <p>Farklı bir arama deneyin veya filtreleri temizleyerek tüm kayıtları görüntüleyin.</p>
                        <div class="empty-actions">
                            <a class="btn btn--primary btn--sm" href="ekle.php">
                                <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                                Yeni randevu
                            </a>
                            <a class="btn btn--ghost btn--sm" href="index.php">Filtreleri temizle</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="appt-table">
                            <thead>
                                <tr>
                                    <th scope="col">Hasta</th>
                                    <th scope="col">Poliklinik</th>
                                    <th scope="col">Tarih &amp; Saat</th>
                                    <th scope="col">Durum</th>
                                    <th scope="col"><span class="sr-only">İşlemler</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($randevular as $randevu):
                                    $hizmet = hizmet_bilgisi((string) $randevu['hizmet']);
                                    $durum = durum_bilgisi((string) $randevu['durum']);
                                    $personel = personel_bilgisi((string) $randevu['personel']);

                                    $detay = [
                                        'ad' => (string) $randevu['ad_soyad'],
                                        'telefon' => (string) $randevu['telefon'],
                                        'eposta' => (string) ($randevu['eposta'] ?? '') !== '' ? (string) $randevu['eposta'] : 'Belirtilmedi',
                                        'hizmet' => $hizmet['ad'],
                                        'tarih' => turkce_tarih((string) $randevu['tarih'], true),
                                        'saat' => (string) $randevu['saat'],
                                        'durum' => $durum['ad'],
                                        'durum_renk' => $durum['renk'],
                                        'personel' => $personel['ad'] . ' · ' . $personel['rol'],
                                        'not' => (string) ($randevu['not'] ?? '') !== '' ? (string) $randevu['not'] : 'Not eklenmemiş.',
                                        'ucret' => para_formatla(doktor_ucreti((string) $randevu['hizmet'], (string) $randevu['personel'])),
                                        'sure' => $hizmet['sure'] . ' dakika',
                                        'avatar' => bas_harfler((string) $randevu['ad_soyad']),
                                        'tone' => avatar_rengi((string) $randevu['ad_soyad']),
                                    ];
                                    $detayJson = json_encode($detay, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT);
                                    ?>
                                    <tr class="appt-row" data-detay="<?= e($detayJson) ?>">
                                        <td data-label="Hasta">
                                            <div class="customer">
                                                <span class="avatar tone-<?= e($detay['tone']) ?>"><?= e($detay['avatar']) ?></span>
                                                <span class="customer-text">
                                                    <strong><?= e($randevu['ad_soyad']) ?></strong>
                                                    <span><?= e($randevu['telefon']) ?></span>
                                                </span>
                                            </div>
                                        </td>

                                        <td data-label="Poliklinik">
                                            <div class="service-cell">
                                                <span class="service-icon tone-<?= e($hizmet['renk']) ?>">
                                                    <svg class="icon" aria-hidden="true"><use href="#icon-<?= e($hizmet['ikon']) ?>"></use></svg>
                                                </span>
                                                <span>
                                                    <strong><?= e($hizmet['kisa']) ?></strong>
                                                    <small><?= (int) $hizmet['sure'] ?> dk · <?= e(para_formatla(doktor_ucreti((string) $randevu['hizmet'], (string) $randevu['personel']))) ?></small>
                                                </span>
                                            </div>
                                        </td>

                                        <td data-label="Tarih &amp; Saat">
                                            <div class="date-cell">
                                                <strong><?= e(gun_etiketi((string) $randevu['tarih'])) ?></strong>
                                                <span><?= e(turkce_tarih((string) $randevu['tarih'])) ?> · <?= e($randevu['saat']) ?></span>
                                            </div>
                                        </td>

                                        <td data-label="Durum">
                                            <span class="status-badge tone-<?= e($durum['renk']) ?>">
                                                <svg class="icon" aria-hidden="true"><use href="#icon-<?= e($durum['ikon']) ?>"></use></svg>
                                                <?= e($durum['ad']) ?>
                                            </span>
                                        </td>

                                        <td data-label="İşlem">
                                            <div class="row-actions">
                                                <button type="button" class="icon-btn" data-detay-ac title="Randevu detayı" aria-label="<?= e($randevu['ad_soyad']) ?> randevu detayı">
                                                    <svg class="icon" aria-hidden="true"><use href="#icon-eye"></use></svg>
                                                </button>

                                                <?php if ($randevu['durum'] === 'bekliyor'): ?>
                                                    <form method="post" action="durum.php" class="inline-form">
                                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="id" value="<?= (int) $randevu['id'] ?>">
                                                        <input type="hidden" name="durum" value="onaylandi">
                                                        <input type="hidden" name="donus" value="<?= e($donus) ?>">
                                                        <button type="submit" class="icon-btn icon-btn--ok" title="Randevuyu onayla" aria-label="<?= e($randevu['ad_soyad']) ?> randevusunu onayla">
                                                            <svg class="icon" aria-hidden="true"><use href="#icon-check"></use></svg>
                                                        </button>
                                                    </form>
                                                <?php elseif ($randevu['durum'] === 'onaylandi'): ?>
                                                    <form method="post" action="durum.php" class="inline-form">
                                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="id" value="<?= (int) $randevu['id'] ?>">
                                                        <input type="hidden" name="durum" value="tamamlandi">
                                                        <input type="hidden" name="donus" value="<?= e($donus) ?>">
                                                        <button type="submit" class="icon-btn icon-btn--ok" title="Tamamlandı olarak işaretle" aria-label="<?= e($randevu['ad_soyad']) ?> randevusunu tamamlandı olarak işaretle">
                                                            <svg class="icon" aria-hidden="true"><use href="#icon-check-circle"></use></svg>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="post" action="sil.php" class="inline-form"
                                                      data-onay
                                                      data-onay-baslik="Randevu silinsin mi?"
                                                      data-onay-metin="<?= e($randevu['ad_soyad']) ?> adlı hastanın <?= e(turkce_tarih((string) $randevu['tarih'])) ?> tarihli randevusu kalıcı olarak silinecek."
                                                      data-onay-buton="Evet, sil">
                                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $randevu['id'] ?>">
                                                    <input type="hidden" name="donus" value="<?= e($donus) ?>">
                                                    <button type="submit" class="icon-btn icon-btn--danger" title="Randevuyu sil" aria-label="<?= e($randevu['ad_soyad']) ?> randevusunu sil">
                                                        <svg class="icon" aria-hidden="true"><use href="#icon-trash"></use></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="sidebar" aria-label="Randevu özeti ve takvim">
                <section class="side-card next-card">
                    <div class="side-card__head">
                        <h3>Sıradaki muayene</h3>
                        <span class="next-badge">yaklaşan</span>
                    </div>

                    <?php if ($sonraki && $sonrakiHizmet && $sonrakiPersonel): ?>
                        <span class="next-label"><?= e(gun_etiketi((string) $sonraki['tarih'])) ?> · <?= e(turkce_tarih((string) $sonraki['tarih'])) ?></span>
                        <div class="next-time"><?= e($sonraki['saat']) ?></div>
                        <div class="next-person">
                            <span class="avatar avatar--sm"><?= e(bas_harfler((string) $sonraki['ad_soyad'])) ?></span>
                            <span>
                                <strong><?= e($sonraki['ad_soyad']) ?></strong>
                                <small><?= e($sonrakiHizmet['kisa']) ?> · <?= e($sonrakiHizmet['sure']) ?> dk</small>
                            </span>
                            <span class="next-price"><?= e(para_formatla(doktor_ucreti((string) $sonraki['hizmet'], (string) $sonraki['personel']))) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="next-empty">
                            <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                            <p>Yaklaşan bir muayene yok. Yeni bir randevu oluşturarak gününüzü planlayın.</p>
                            <a class="btn btn--light btn--sm" href="ekle.php">Yeni randevu</a>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="side-card">
                    <div class="side-card__head">
                        <h3>Durum dağılımı</h3>
                        <span class="side-card__meta"><?= (int) $istatistik['toplam'] ?> kayıt</span>
                    </div>

                    <div class="donut-wrap">
                        <div class="donut" style="background: <?= e($donutGradyan) ?>">
                            <div class="donut-center">
                                <strong><?= (int) $istatistik['toplam'] ?></strong>
                                <small>toplam</small>
                            </div>
                        </div>
                        <ul class="legend">
                            <?php foreach ($durumListesi as $anahtar => $durum): ?>
                                <li>
                                    <span class="status-dot" style="--dot: <?= e($durum['dot']) ?>"></span>
                                    <?= e($durum['ad']) ?>
                                    <b><?= (int) ($istatistik['durumlar'][$anahtar] ?? 0) ?></b>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>

                <section class="side-card calendar-card">
                    <div class="side-card__head">
                        <h3>Takvim</h3>
                        <div class="cal-nav">
                            <a href="<?= e(filtre_url(['takvim' => $takvim['onceki']], ['tarih'])) ?>" aria-label="Önceki ay">
                                <svg class="icon" aria-hidden="true"><use href="#icon-chevron-left"></use></svg>
                            </a>
                            <a href="<?= e(filtre_url(['takvim' => $takvim['sonraki']], ['tarih'])) ?>" aria-label="Sonraki ay">
                                <svg class="icon" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                            </a>
                        </div>
                    </div>

                    <div class="cal-title"><?= e($takvim['etiket']) ?></div>

                    <div class="cal-grid">
                        <?php foreach (['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $gunAdi): ?>
                            <span class="cal-dow"><?= e($gunAdi) ?></span>
                        <?php endforeach; ?>

                        <?php foreach ($takvim['haftalar'] as $hafta): ?>
                            <?php foreach ($hafta as $gun): ?>
                                <?php
                                $siniflar = ['cal-day'];
                                if (!$gun['ayda']) $siniflar[] = 'is-outside';
                                if ($gun['bugun']) $siniflar[] = 'is-today';
                                if ($gun['secili']) $siniflar[] = 'is-selected';
                                if ($gun['sayi'] > 0) $siniflar[] = 'has-appt';
                                $etiket = turkce_tarih($gun['tarih']) . ($gun['sayi'] > 0 ? ' · ' . $gun['sayi'] . ' randevu' : '');
                                ?>
                                <a class="<?= e(implode(' ', $siniflar)) ?>"
                                   href="<?= e(filtre_url(['tarih' => $gun['tarih']])) ?>"
                                   title="<?= e($etiket) ?>"
                                   aria-label="<?= e($etiket) ?>"
                                   <?= $gun['bugun'] ? 'aria-current="date"' : '' ?>>
                                    <?= (int) $gun['gun'] ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="cal-foot">
                        <span><span class="cal-dot"></span> Randevu var</span>
                        <?php if ($takvim['bugun_ay'] !== sprintf('%04d-%02d', $takvim['yil'], $takvim['ay'])): ?>
                            <a href="<?= e(filtre_url(['takvim' => $takvim['bugun_ay']])) ?>">Bugüne dön</a>
                        <?php else: ?>
                            <a href="<?= e(filtre_url(['tarih' => date('Y-m-d')])) ?>">Bugünü gör</a>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="side-card">
                    <div class="side-card__head">
                        <h3>Hızlı işlemler</h3>
                    </div>
                    <div class="quick-actions">
                        <a class="quick-link" href="<?= e(filtre_url(['tarih' => date('Y-m-d')])) ?>">
                            <svg class="icon" aria-hidden="true"><use href="#icon-sun"></use></svg>
                            Bugünün randevuları
                        </a>
                        <a class="quick-link" href="<?= e(filtre_url(['durum' => 'bekliyor'], ['tarih'])) ?>">
                            <svg class="icon" aria-hidden="true"><use href="#icon-clock"></use></svg>
                            Onay bekleyenler
                        </a>
                        <a class="quick-link" href="ekle.php">
                            <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                            Yeni randevu oluştur
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</section>

<!-- HİZMETLER -->
<section class="section services-section" id="hizmetler">
    <div class="shell">
        <div class="section-head">
            <div class="section-head__text">
                <span class="kicker">Poliklinik rehberi</span>
                <h2>İhtiyacınıza uygun <span class="grad-text">polikliniği seçin</span></h2>
                <p>Bölümü seçin; süre ve Op. Dr. taban ücretiyle birlikte saniyeler içinde randevunuzu oluşturun. Doç. Dr. ve Prof. Dr. seçiminde ücret artar.</p>
            </div>
            <span class="section-count"><?= count($hizmetListesi) ?> poliklinik · <?= count(personeller()) - 1 ?> doktor</span>
        </div>

        <div class="services-grid">
            <?php $sira = 0; foreach ($hizmetListesi as $anahtar => $hizmet): $sira++; ?>
                <article class="service-card tone-<?= e($hizmet['renk']) ?> reveal reveal--d<?= $sira % 4 ?>">
                    <?php if ($anahtar === 'dahiliye'): ?>
                        <span class="service-card__badge">Yoğun</span>
                    <?php endif; ?>
                    <span class="service-card__icon">
                        <svg class="icon" aria-hidden="true"><use href="#icon-<?= e($hizmet['ikon']) ?>"></use></svg>
                    </span>
                    <h3><?= e($hizmet['ad']) ?></h3>
                    <p><?= e($hizmet['aciklama']) ?></p>
                    <div class="service-card__foot">
                        <span class="service-meta"><?= (int) $hizmet['sure'] ?> dk · <?= e(para_formatla($hizmet['fiyat'])) ?> taban · <?= count(poliklinik_doktorlari($anahtar)) ?> doktor</span>
                        <a class="service-link" href="ekle.php?hizmet=<?= e($anahtar) ?>" aria-label="<?= e($hizmet['ad']) ?> için randevu al">
                            <svg class="icon" aria-hidden="true"><use href="#icon-arrow-right"></use></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- NEDEN YONCA HASTANESİ -->
<section class="section why-section" id="ozellikler">
    <div class="shell">
        <div class="why-panel reveal">
            <div class="why-visual">
                <div>
                    <span class="kicker kicker--light">Neden Yonca Hastanesi?</span>
                    <h2>Sağlığınız güvence altında,<br>uzman ellerdesiniz.</h2>
                    <p>Modern tıbbi donanımımız, alanında uzman hekim kadromuz ve hasta odaklı yaklaşımımızla sağlığınız için en iyisini sunuyoruz. Sizin sağlığınız, bizim önceliğimiz.</p>
                </div>
                <p class="why-quote">“Sağlıklı ve mutlu bir gelecek, doğru adreste başlar.”</p>
            </div>

            <div class="why-list">
                <div class="why-item">
                    <span class="why-icon tone-teal"><svg class="icon" aria-hidden="true"><use href="#icon-stethoscope"></use></svg></span>
                    <div>
                        <h3>Uzman Hekim Kadrosu</h3>
                        <p>Alanında deneyimli doktorlarımız ve güler yüzlü sağlık personelimizle teşhis ve tedavide en yüksek standartları sağlıyoruz.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="why-icon tone-blue"><svg class="icon" aria-hidden="true"><use href="#icon-shield"></use></svg></span>
                    <div>
                        <h3>Güvenilir ve Modern Altyapı</h3>
                        <p>İleri teknoloji cihazlarla donatılmış polikliniklerimizde, en doğru tanı ve modern tedavi imkanlarını sunuyoruz.</p>
                    </div>
                </div>
                <div class="why-item">
                    <span class="why-icon tone-mint"><svg class="icon" aria-hidden="true"><use href="#icon-heart-pulse"></use></svg></span>
                    <div>
                        <h3>Hasta Odaklı Hizmet</h3>
                        <p>Sıcak, steril ve konforlu hastane ortamımızda, iyileşme sürecinizin her adımında yanınızdayız.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
