<?php
/**
 * YoncaHastanesi — Yeni randevu oluşturma formu
 */
require_once __DIR__ . '/functions.php';
oturum_hazirla();

$hatalar = [];
$genelHata = '';
$hizmetListesi = hizmetler();
$durumListesi = durumlar();

$veri = [
    'ad_soyad' => '',
    'telefon'  => '',
    'eposta'   => '',
    'hizmet'   => '',
    'tarih'    => date('Y-m-d'),
    'saat'     => '',
    'personel' => 'farketmez',
    'not'      => '',
    'sms'      => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sonuc = randevu_dogrula($_POST, randevulari_getir());
    $veri = $sonuc['veri'];
    $hatalar = $sonuc['hatalar'];

    if (!csrf_dogrula($_POST['csrf'] ?? null)) {
        $genelHata = 'Oturum doğrulaması başarısız oldu. Sayfayı yenileyip formu tekrar gönderebilirsiniz.';
        flash_ekle('hata', 'Oturum süresi doldu, lütfen tekrar deneyin.');
    } elseif (!$hatalar) {
        $yeniId = randevu_ekle($veri);
        flash_ekle('basari', 'Randevunuz oluşturuldu. Onay bekleyenler listesine eklendi.');
        yonlendir('index.php?eklendi=' . $yeniId . '#randevular');
    } else {
        // Hata varsa bildirim çıkartan kısım burası
        flash_ekle('hata', 'Formda eksik veya hatalı alanlar var. Lütfen bilgileri kontrol edin.');
    }
} else {
    $onHizmet = (string) ($_GET['hizmet'] ?? '');
    if (isset($hizmetListesi[$onHizmet])) {
        $veri['hizmet'] = $onHizmet;
    }
}

$seciliHizmet = $veri['hizmet'] !== '' ? hizmet_bilgisi($veri['hizmet']) : null;
$seciliPersonel = personel_bilgisi($veri['personel']);
$seciliUcret = $veri['hizmet'] !== '' ? doktor_ucreti($veri['hizmet'], $veri['personel']) : 0;
$bugunMu = $veri['tarih'] === date('Y-m-d');
$simdi = time();
$uygunSlotVar = false;

foreach (saat_dilimleri() as $slot) {
    $slotPast = $bugunMu && strtotime($veri['tarih'] . ' ' . $slot) <= $simdi;
    if (!$slotPast) {
        $uygunSlotVar = true;
        break;
    }
}

$hataSayisi = count($hatalar) + ($genelHata !== '' ? 1 : 0);
$sayfa_baslik = 'Yeni Randevu — Yonca Hastanesi';
$meta_aciklama = 'Yonca Hastanesi üzerinde yeni bir randevu oluşturun: hasta bilgileri, poliklinik, doktor, tarih ve saat seçimi tek formda.';
$aktif_sayfa = 'yeni';
$cta_goster = false;
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="shell">
        <nav class="breadcrumb" aria-label="Sayfa yolu">
            <a href="index.php">Genel bakış</a>
            <svg class="icon" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
            <span>Yeni randevu</span>
        </nav>

        <h1>Yeni randevu <em class="grad-text">oluşturun.</em></h1>
        <p>Hasta bilgilerini, polikliniği ve uygun saat dilimini seçin. Formu gönderdiğinizde randevu “Bekliyor” durumuyla listeye eklenir.</p>

        <div class="meta-row">
            <span class="meta-pill"><svg class="icon" aria-hidden="true"><use href="#icon-clock"></use></svg> Ortalama 2 dakika</span>
            <span class="meta-pill"><svg class="icon" aria-hidden="true"><use href="#icon-shield"></use></svg> Sunucu tarafı doğrulama</span>
            <span class="meta-pill"><svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg> Anında takvimde</span>
        </div>
    </div>
</section>

<section class="section booking-section">
    <div class="shell">
        <?php if ($hataSayisi > 0): ?>
            <div class="alert alert--danger booking-alert" id="form-hatasi" role="alert">
                <svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg>
                <div>
                    <strong>Formda <?= $hataSayisi ?> eksik veya hatalı alan var.</strong>
                    <?php if ($genelHata !== ''): ?>
                        <p><?= e($genelHata) ?></p>
                    <?php endif; ?>
                    <?php if ($hatalar): ?>
                        <ul>
                            <?php foreach ($hatalar as $hata): ?>
                                <li><?= e($hata) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="booking-layout">
            <form class="booking-form" method="post" action="ekle.php" data-booking-form autocomplete="on" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <section class="form-card reveal">
                    <div class="form-step-head">
                        <span class="step-number">01</span>
                        <div>
                            <h2>Hasta bilgileri</h2>
                            <p>Randevunun kime ait olduğunu ve iletişim bilgilerini girin.</p>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="adSoyad">Ad soyad <span class="req">*</span></label>
                            <div class="input-wrap">
                                <svg class="icon" aria-hidden="true"><use href="#icon-user"></use></svg>
                                <input type="text" id="adSoyad" name="ad_soyad" value="<?= e($veri['ad_soyad']) ?>"
                                       placeholder="Örn. Elif Yılmaz" maxlength="80" required
                                       class="<?= isset($hatalar['ad_soyad']) ? 'is-invalid' : '' ?>"
                                       aria-invalid="<?= isset($hatalar['ad_soyad']) ? 'true' : 'false' ?>"
                                       aria-describedby="<?= isset($hatalar['ad_soyad']) ? 'hataAdSoyad' : '' ?>">
                            </div>
                            <?php if (isset($hatalar['ad_soyad'])): ?>
                                <p class="field-error" id="hataAdSoyad"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['ad_soyad']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="telefon">Telefon <span class="req">*</span></label>
                            <div class="input-wrap">
                                <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                                <input type="tel" id="telefon" name="telefon" value="<?= e($veri['telefon']) ?>"
                                       placeholder="0532 418 72 90" maxlength="20" required
                                       class="<?= isset($hatalar['telefon']) ? 'is-invalid' : '' ?>"
                                       aria-invalid="<?= isset($hatalar['telefon']) ? 'true' : 'false' ?>"
                                       aria-describedby="<?= isset($hatalar['telefon']) ? 'hataTelefon' : 'telefonIpucu' ?>">
                            </div>
                            <?php if (isset($hatalar['telefon'])): ?>
                                <p class="field-error" id="hataTelefon"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['telefon']) ?></p>
                            <?php else: ?>
                                <p class="field-hint" id="telefonIpucu">05XX XXX XX XX biçiminde girin.</p>
                            <?php endif; ?>
                        </div>

                        <div class="field field--full">
                            <label for="eposta">E-posta <span class="opt">isteğe bağlı</span></label>
                            <div class="input-wrap">
                                <svg class="icon" aria-hidden="true"><use href="#icon-mail"></use></svg>
                                <input type="email" id="eposta" name="eposta" value="<?= e($veri['eposta']) ?>"
                                       placeholder="ornek@eposta.com" maxlength="120"
                                       class="<?= isset($hatalar['eposta']) ? 'is-invalid' : '' ?>"
                                       aria-invalid="<?= isset($hatalar['eposta']) ? 'true' : 'false' ?>"
                                       aria-describedby="<?= isset($hatalar['eposta']) ? 'hataEposta' : '' ?>">
                            </div>
                            <?php if (isset($hatalar['eposta'])): ?>
                                <p class="field-error" id="hataEposta"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['eposta']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="form-card reveal">
                    <div class="form-step-head">
                        <span class="step-number">02</span>
                        <div>
                            <h2>Poliklinik seçimi</h2>
                            <p>Muayene edilecek polikliniği seçin; süre ve taban ücret özet panelinde anında güncellenir. Doktor unvanı ücrete eklenir.</p>
                        </div>
                    </div>

                    <div class="service-options" role="radiogroup" aria-label="Poliklinik seçimi">
                        <?php foreach ($hizmetListesi as $anahtar => $hizmet): ?>
                            <label class="service-option tone-<?= e($hizmet['renk']) ?>">
                                <input type="radio" name="hizmet" value="<?= e($anahtar) ?>"
                                       data-ad="<?= e($hizmet['ad']) ?>"
                                       data-sure="<?= (int) $hizmet['sure'] ?>"
                                       data-fiyat="<?= (int) $hizmet['fiyat'] ?>"
                                       data-ikon="<?= e($hizmet['ikon']) ?>"
                                       data-tone="<?= e($hizmet['renk']) ?>"
                                       <?= $veri['hizmet'] === $anahtar ? 'checked' : '' ?> required>
                                <span class="service-option__inner">
                                    <span class="service-option__icon">
                                        <svg class="icon" aria-hidden="true"><use href="#icon-<?= e($hizmet['ikon']) ?>"></use></svg>
                                    </span>
                                    <span class="service-option__body">
                                        <strong><?= e($hizmet['kisa']) ?></strong>
                                        <small><?= (int) $hizmet['sure'] ?> dk · <?= e(para_formatla($hizmet['fiyat'])) ?> taban</small>
                                    </span>
                                    <span class="service-option__check">
                                        <svg class="icon" aria-hidden="true"><use href="#icon-check"></use></svg>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if (isset($hatalar['hizmet'])): ?>
                        <p class="field-error" id="hataHizmet"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['hizmet']) ?></p>
                    <?php endif; ?>
                </section>

                <section class="form-card reveal">
                    <div class="form-step-head">
                        <span class="step-number">03</span>
                        <div>
                            <h2>Tarih, saat ve doktor</h2>
                            <p>Uygun günü ve saat dilimini seçin. Dolu saatler otomatik olarak kontrol edilir.</p>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="tarih">Randevu tarihi <span class="req">*</span></label>
                            <div class="input-wrap">
                                <svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg>
                                <input type="date" id="tarih" name="tarih" value="<?= e($veri['tarih']) ?>"
                                       min="<?= e(date('Y-m-d')) ?>" max="<?= e(date('Y-m-d', strtotime('+1 year'))) ?>" required
                                       data-booking-date
                                       class="<?= isset($hatalar['tarih']) ? 'is-invalid' : '' ?>"
                                       aria-invalid="<?= isset($hatalar['tarih']) ? 'true' : 'false' ?>"
                                       aria-describedby="<?= isset($hatalar['tarih']) ? 'hataTarih' : '' ?>">
                            </div>
                            <?php if (isset($hatalar['tarih'])): ?>
                                <p class="field-error" id="hataTarih"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['tarih']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="personel">Doktor <span class="opt">isteğe bağlı</span></label>
                            <div class="select-wrap">
                                <select id="personel" name="personel" data-booking-staff>
                                    <option value="farketmez" data-zam="0" <?= $veri['personel'] === 'farketmez' ? 'selected' : '' ?>>Fark etmez</option>
                                    <?php foreach ($hizmetListesi as $hizmetAnahtar => $hizmet): ?>
                                        <?php $doktorlar = poliklinik_doktorlari($hizmetAnahtar); ?>
                                        <?php if (!$doktorlar) { continue; } ?>
                                        <optgroup label="<?= e($hizmet['kisa']) ?>" data-hizmet="<?= e($hizmetAnahtar) ?>">
                                            <?php foreach ($doktorlar as $anahtar => $personel): ?>
                                                <option value="<?= e($anahtar) ?>" data-zam="<?= (int) doktor_zam_orani($anahtar) ?>" <?= $veri['personel'] === $anahtar ? 'selected' : '' ?>>
                                                    <?= e($personel['ad']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="icon" aria-hidden="true"><use href="#icon-chevron-down"></use></svg>
                            </div>
                            <p class="field-hint">Doç. Dr. seçiminde +%25, Prof. Dr. seçiminde +%50 ücret farkı uygulanır.</p>
                        </div>

                        <div class="field field--full">
                            <label>Saat dilimi <span class="req">*</span></label>
                            <div class="time-grid" data-time-grid role="radiogroup" aria-label="Saat dilimi seçimi">
                                <?php foreach (saat_dilimleri() as $slot):
                                    $slotPast = $bugunMu && strtotime($veri['tarih'] . ' ' . $slot) <= $simdi;
                                    ?>
                                    <label class="time-chip <?= $slotPast ? 'is-past' : '' ?>">
                                        <input type="radio" name="saat" value="<?= e($slot) ?>"
                                               data-slot="<?= e($slot) ?>"
                                               <?= $veri['saat'] === $slot ? 'checked' : '' ?>
                                               <?= $slotPast ? 'disabled' : 'required' ?>>
                                        <span><?= e($slot) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($hatalar['saat'])): ?>
                                <p class="field-error" id="hataSaat"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['saat']) ?></p>
                            <?php elseif ($bugunMu && !$uygunSlotVar): ?>
                                <p class="field-hint">Bugün için uygun saat kalmadı. Lütfen başka bir gün seçin.</p>
                            <?php else: ?>
                                <p class="field-hint">Randevular 08:30 – 17:30 arasında, 30 dakikalık dilimler halindedir.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="form-card reveal">
                    <div class="form-step-head">
                        <span class="step-number">04</span>
                        <div>
                            <h2>Notlar ve tercihler</h2>
                            <p>Hastaya özel talepleri ekleyin; hekim randevu öncesinde görsün.</p>
                        </div>
                    </div>

                    <div class="field">
                        <label for="not">Hasta notu <span class="opt">isteğe bağlı</span></label>
                        <textarea id="not" name="not" maxlength="500" rows="4" data-not-area
                                  placeholder="Örn. Tansiyon takibi, alerji bilgisi veya kontrol notu..."
                                  class="<?= isset($hatalar['not']) ? 'is-invalid' : '' ?>"
                                  aria-invalid="<?= isset($hatalar['not']) ? 'true' : 'false' ?>"><?= e($veri['not']) ?></textarea>
                        <div class="field-foot">
                            <span class="field-hint">En fazla 500 karakter.</span>
                            <span class="char-count" data-char-count>0 / 500</span>
                        </div>
                        <?php if (isset($hatalar['not'])): ?>
                            <p class="field-error"><svg class="icon" aria-hidden="true"><use href="#icon-alert"></use></svg><?= e($hatalar['not']) ?></p>
                        <?php endif; ?>
                    </div>

                    <label class="checkline">
                        <input type="checkbox" name="sms" value="1" <?= !empty($veri['sms']) ? 'checked' : '' ?>>
                        <span>Hastaya randevu hatırlatması SMS ile gönderilsin.</span>
                    </label>

                    <div class="form-actions">
                        <span class="form-actions__note">
                            <svg class="icon" aria-hidden="true"><use href="#icon-shield"></use></svg>
                            Bilgileriniz güvenli biçimde işlenir.
                        </span>
                        <div class="form-actions__buttons">
                            <a class="btn btn--ghost" href="index.php">Vazgeç</a>
                            <button class="btn btn--primary" type="submit">
                                <svg class="icon" aria-hidden="true"><use href="#icon-calendar-plus"></use></svg>
                                Randevuyu oluştur
                            </button>
                        </div>
                    </div>
                </section>
            </form>

            <aside class="booking-side">
                <section class="summary-card reveal reveal--d1" aria-label="Randevu özeti">
                    <div class="summary-head">
                        <h2>Randevu özeti</h2>
                        <span class="summary-live">canlı</span>
                    </div>

                    <div class="summary-service">
                        <span class="summary-service__icon" data-summary-icon-wrap>
                            <svg class="icon" aria-hidden="true"><use href="#icon-<?= e($seciliHizmet['ikon'] ?? 'stethoscope') ?>" data-summary-icon></use></svg>
                        </span>
                        <span>
                            <strong data-summary-service><?= e($seciliHizmet['ad'] ?? 'Poliklinik seçin') ?></strong>
                            <small data-summary-meta><?= $seciliHizmet ? (int) $seciliHizmet['sure'] . ' dk · ' . e(para_formatla($seciliUcret)) : 'Henüz poliklinik seçilmedi' ?></small>
                        </span>
                    </div>

                    <ul class="summary-lines">
                        <li><span>Hasta</span><strong data-summary-name><?= $veri['ad_soyad'] !== '' ? e($veri['ad_soyad']) : '—' ?></strong></li>
                        <li><span>Tarih</span><strong data-summary-date><?= $veri['tarih'] !== '' ? e(turkce_tarih($veri['tarih'])) : 'Tarih seçin' ?></strong></li>
                        <li><span>Saat</span><strong data-summary-time><?= $veri['saat'] !== '' ? e($veri['saat']) : 'Saat seçin' ?></strong></li>
                        <li><span>Doktor</span><strong data-summary-staff><?= e($seciliPersonel['ad']) ?></strong></li>
                        <li><span>Süre</span><strong data-summary-duration><?= $seciliHizmet ? (int) $seciliHizmet['sure'] . ' dakika' : '—' ?></strong></li>
                    </ul>

                    <div class="summary-total">
                        <span>Muayene ücreti</span>
                        <strong data-summary-price><?= e(para_formatla($seciliUcret)) ?></strong>
                    </div>

                    <p class="summary-note">
                        <svg class="icon" aria-hidden="true"><use href="#icon-shield"></use></svg>
                        Formu gönderdiğinizde randevu “Bekliyor” durumuyla oluşturulur ve onay bekler.
                    </p>
                </section>

                <section class="info-card reveal reveal--d2">
                    <h3>İyi bir muayene için</h3>
                    <ul>
                        <li><svg class="icon" aria-hidden="true"><use href="#icon-clock"></use></svg> Hastanın 10 dakika önce gelmesini önerin.</li>
                        <li><svg class="icon" aria-hidden="true"><use href="#icon-note"></use></svg> Alerji ve kronik hastalık notlarını mutlaka ekleyin.</li>
                        <li><svg class="icon" aria-hidden="true"><use href="#icon-calendar"></use></svg> Dolu saatler otomatik olarak engellenir.</li>
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</section>
<?php if ($hataSayisi > 0): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const hataKutusu = document.getElementById('form-hatasi');
        if (hataKutusu) {
            hataKutusu.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
