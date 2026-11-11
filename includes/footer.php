<?php
/**
 * YoncaHastanesi — Ortak alt bölüm (CTA, footer, modallar, scriptler)
 */
$cta_goster = $cta_goster ?? true;
$hizmetListesi = hizmetler();
?>
</main>

<?php if ($cta_goster): ?>
    <section class="cta-band">
        <div class="shell">
            <div class="cta-inner reveal">
                <span class="cta-orb cta-orb--1" aria-hidden="true"></span>
                <span class="cta-orb cta-orb--2" aria-hidden="true"></span>
                <div class="cta-copy">
                    <span class="kicker kicker--light">Bir sonraki adım</span>
                    <h2>Hasta akışını<br>kolaylaştırın.</h2>
                    <p>Yeni randevuyu saniyeler içinde oluşturun; onay, takip ve günlük özet tek panelde sizinle olsun.</p>
                </div>
                <div class="cta-actions">
                    <a class="btn btn--light" href="ekle.php">
                        <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                        Randevu oluştur
                    </a>
                    <a class="cta-link" href="index.php#hizmetler">
                        Poliklinikleri keşfet
                        <svg class="icon" aria-hidden="true"><use href="#icon-arrow-right"></use></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<footer class="site-footer" id="iletisim">
    <div class="shell">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="brand brand--footer" href="index.php" aria-label="Yonca Hastanesi ana sayfa">
                    <span class="brand-mark">
                        <svg class="icon" aria-hidden="true"><use href="#icon-medical-cross"></use></svg>
                    </span>
                    <span class="brand-text">
                        <strong>Yonca<span>Hastanesi</span></strong>
                        <small>hastane randevu sistemi</small>
                    </span>
                </a>
                <p>Hastane poliklinikleri için tasarlanmış, hızlı ve güvenli randevu yönetim deneyimi. Hastayı, hekimi ve zamanı tek panelde buluşturur.</p>
                <div class="footer-badges">
                    <span>PHP 8</span>
                    <span>Oturum tabanlı</span>
                    <span>Mobil uyumlu</span>
                </div>
            </div>

            <div class="footer-col">
                <h3>Keşfet</h3>
                <ul>
                    <li><a href="index.php">Genel bakış</a></li>
                    <li><a href="index.php#randevular">Randevu listesi</a></li>
                    <li><a href="index.php#hizmetler">Poliklinikler</a></li>
                    <li><a href="ekle.php">Yeni randevu</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Poliklinikler</h3>
                <ul>
                    <?php foreach (array_slice($hizmetListesi, 0, 5, true) as $anahtar => $hizmet): ?>
                        <li><a href="ekle.php?hizmet=<?= e($anahtar) ?>"><?= e($hizmet['kisa']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h3>İletişim</h3>
                <ul class="footer-contact">
                    <li>
                        <svg class="icon" aria-hidden="true"><use href="#icon-map-pin"></use></svg>
                        <span>Sağlık Bulvarı No: 24<br>Ataşehir / İstanbul</span>
                    </li>
                    <li>
                        <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                        <a href="tel:+905389109005">+90 538 910 90 05</a>
                    </li>
                    <li>
                        <svg class="icon" aria-hidden="true"><use href="#icon-mail"></use></svg>
                        <a href="mailto:Yonca Hastanesi@gmail.com">Yonca Hastanesi@gmail.com</a>
                    </li>
                    <li>
                        <svg class="icon" aria-hidden="true"><use href="#icon-clock"></use></svg>
                        <span>Pazartesi – Cumartesi<br>08:30 – 17:30</span>
                    </li>
                </ul>

                <form method="post" action="sifirla.php" class="footer-reset-form"
                      data-onay
                      data-onay-baslik="Demo verileri sıfırlansın mı?"
                      data-onay-metin="Eklediğiniz tüm randevular silinir ve örnek kayıtlar yeniden yüklenir."
                      data-onay-buton="Evet, sıfırla">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="donus" value="index.php">
                    <button type="submit" class="footer-reset">Demo verilerini sıfırla</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Yonca Hastanesi. Tüm hakları saklıdır.</span>
        </div>
    </div>
</footer>

<!-- Silme / onay modalı -->
<div class="modal" id="onayModal" aria-hidden="true">
    <div class="modal__backdrop" data-modal-kapat></div>
    <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="onayBaslik">
        <button type="button" class="modal__close" data-modal-kapat aria-label="Pencereyi kapat">
            <svg class="icon" aria-hidden="true"><use href="#icon-close"></use></svg>
        </button>
        <div class="modal__icon modal__icon--danger">
            <svg class="icon" aria-hidden="true"><use href="#icon-trash"></use></svg>
        </div>
        <h2 id="onayBaslik" data-onay-baslik>Randevu silinsin mi?</h2>
        <p data-onay-metin>Bu işlem geri alınamaz.</p>

        <form method="post" action="sil.php" data-onay-formu>
            <div data-onay-alanlar></div>
            <div class="modal__actions">
                <button type="button" class="btn btn--ghost" data-modal-kapat>Vazgeç</button>
                <button type="submit" class="btn btn--danger" data-onay-gonder>
                    <svg class="icon" aria-hidden="true"><use href="#icon-trash"></use></svg>
                    <span data-onay-buton>Evet, sil</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Randevu detay modalı -->
<div class="modal" id="detayModal" aria-hidden="true">
    <div class="modal__backdrop" data-modal-kapat></div>
    <div class="modal__panel modal__panel--wide" role="dialog" aria-modal="true" aria-labelledby="detayBaslik">
        <button type="button" class="modal__close" data-modal-kapat aria-label="Pencereyi kapat">
            <svg class="icon" aria-hidden="true"><use href="#icon-close"></use></svg>
        </button>

        <div class="detail-head">
            <span class="avatar avatar--lg tone-teal" data-detay-avatar>--</span>
            <div>
                <span class="status-badge tone-amber" data-detay-durum>Bekliyor</span>
                <h2 id="detayBaslik" data-detay-ad>Hasta</h2>
                <p data-detay-telefon>—</p>
            </div>
        </div>

        <div class="detail-grid">
            <div>
                <span>Poliklinik</span>
                <strong data-detay-hizmet>—</strong>
            </div>
            <div>
                <span>Muayene ücreti</span>
                <strong data-detay-fiyat>—</strong>
            </div>
            <div>
                <span>Tarih</span>
                <strong data-detay-tarih>—</strong>
            </div>
            <div>
                <span>Saat</span>
                <strong data-detay-saat>—</strong>
            </div>
            <div>
                <span>Doktor</span>
                <strong data-detay-personel>—</strong>
            </div>
            <div>
                <span>Süre</span>
                <strong data-detay-sure>—</strong>
            </div>
            <div class="detail-grid__full">
                <span>E-posta</span>
                <strong data-detay-eposta>—</strong>
            </div>
            <div class="detail-grid__full">
                <span>Hasta notu</span>
                <p data-detay-not>—</p>
            </div>
        </div>

        <div class="modal__actions">
            <button type="button" class="btn btn--ghost" data-modal-kapat>Kapat</button>
        </div>
    </div>
</div>

<script src="assets/js/app.js?v=2.0" defer></script>
</body>
</html>
