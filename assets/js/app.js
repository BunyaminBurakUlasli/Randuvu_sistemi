/**
 * YoncaHastanesi — Arayüz etkileşimleri
 * Mobil menü, modallar, canlı randevu özeti ve küçük animasyonlar.
 */
(function () {
    'use strict';

    var doc = document;
    var azHareket = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ----------------------------------------------------------------------
     * Mobil menü
     * -------------------------------------------------------------------- */
    var header = doc.querySelector('[data-header]');
    var menuButonu = doc.querySelector('[data-menu-degistir]');
    var anaMenu = doc.getElementById('mainNav');

    function menuyuKapat() {
        if (!header || !menuButonu) {
            return;
        }

        header.classList.remove('is-open');
        menuButonu.setAttribute('aria-expanded', 'false');
        menuButonu.setAttribute('aria-label', 'Menüyü aç');
        doc.body.classList.remove('nav-open');
    }

    if (header && menuButonu && anaMenu) {
        menuButonu.addEventListener('click', function () {
            var acik = header.classList.toggle('is-open');
            menuButonu.setAttribute('aria-expanded', acik ? 'true' : 'false');
            menuButonu.setAttribute('aria-label', acik ? 'Menüyü kapat' : 'Menüyü aç');
            doc.body.classList.toggle('nav-open', acik);
        });

        anaMenu.querySelectorAll('a').forEach(function (baglanti) {
            baglanti.addEventListener('click', menuyuKapat);
        });

        doc.addEventListener('click', function (olay) {
            if (header.classList.contains('is-open') && !header.contains(olay.target)) {
                menuyuKapat();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 980) {
                menuyuKapat();
            }
        });
    }

    /* Header gölgesi */
    function headerDurumu() {
        if (header) {
            header.classList.toggle('is-scrolled', window.scrollY > 12);
        }
    }

    headerDurumu();
    window.addEventListener('scroll', headerDurumu, { passive: true });

    /* ----------------------------------------------------------------------
     * Flash mesajları
     * -------------------------------------------------------------------- */
    doc.querySelectorAll('[data-toast]').forEach(function (toast) {
        var zamanlayici = window.setTimeout(function () {
            toastKapat(toast);
        }, 6000);

        var kapat = toast.querySelector('[data-toast-kapat]');
        if (kapat) {
            kapat.addEventListener('click', function () {
                window.clearTimeout(zamanlayici);
                toastKapat(toast);
            });
        }
    });

    function toastKapat(toast) {
        if (!toast || toast.classList.contains('is-hiding')) {
            return;
        }

        toast.classList.add('is-hiding');
        window.setTimeout(function () {
            toast.remove();
        }, 340);
    }

    /* ----------------------------------------------------------------------
     * Filtre / sıralama otomatik gönderim
     * -------------------------------------------------------------------- */
    doc.querySelectorAll('[data-auto-submit]').forEach(function (alan) {
        alan.addEventListener('change', function () {
            if (alan.form) {
                alan.form.submit();
            }
        });
    });

    /* ----------------------------------------------------------------------
     * Modal yönetimi
     * -------------------------------------------------------------------- */
    var acikModal = null;
    var sonOdak = null;

    function odaklanabilirler(modal) {
        return Array.prototype.slice.call(modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) {
            return el.offsetParent !== null;
        });
    }

    function modalAc(modal) {
        if (!modal) {
            return;
        }

        if (acikModal && acikModal !== modal) {
            modalKapat(acikModal, false);
        }

        sonOdak = doc.activeElement;
        acikModal = modal;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        doc.body.classList.add('modal-open');

        window.setTimeout(function () {
            var odaklar = odaklanabilirler(modal);
            if (odaklar.length) {
                odaklar[0].focus();
            }
        }, 60);
    }

    function modalKapat(modal, odagiGeriVer) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (acikModal === modal) {
            acikModal = null;
        }

        if (!doc.querySelector('.modal.is-open')) {
            doc.body.classList.remove('modal-open');
        }

        if (odagiGeriVer !== false && sonOdak && typeof sonOdak.focus === 'function') {
            sonOdak.focus();
        }
    }

    doc.querySelectorAll('[data-modal-kapat]').forEach(function (kapat) {
        kapat.addEventListener('click', function () {
            modalKapat(kapat.closest('.modal'));
        });
    });

    doc.addEventListener('keydown', function (olay) {
        if (!acikModal) {
            return;
        }

        if (olay.key === 'Escape') {
            olay.preventDefault();
            modalKapat(acikModal);
            return;
        }

        if (olay.key !== 'Tab') {
            return;
        }

        var odaklar = odaklanabilirler(acikModal);
        if (!odaklar.length) {
            return;
        }

        var ilk = odaklar[0];
        var son = odaklar[odaklar.length - 1];

        if (olay.shiftKey && doc.activeElement === ilk) {
            olay.preventDefault();
            son.focus();
        } else if (!olay.shiftKey && doc.activeElement === son) {
            olay.preventDefault();
            ilk.focus();
        }
    });

    /* ----------------------------------------------------------------------
     * Silme / sıfırlama onayı
     * -------------------------------------------------------------------- */
    var onayModal = doc.getElementById('onayModal');
    var onayFormu = onayModal ? onayModal.querySelector('[data-onay-formu]') : null;
    var onayAlanlari = onayModal ? onayModal.querySelector('[data-onay-alanlar]') : null;

    doc.querySelectorAll('form[data-onay]').forEach(function (kaynakForm) {
        kaynakForm.addEventListener('submit', function (olay) {
            if (!onayModal || !onayFormu || !onayAlanlari) {
                return;
            }

            olay.preventDefault();

            onayFormu.setAttribute('action', kaynakForm.getAttribute('action') || 'sil.php');
            onayAlanlari.innerHTML = '';

            kaynakForm.querySelectorAll('input[type="hidden"]').forEach(function (girdi) {
                var kopya = doc.createElement('input');
                kopya.type = 'hidden';
                kopya.name = girdi.name;
                kopya.value = girdi.value;
                onayAlanlari.appendChild(kopya);
            });

            var baslik = onayModal.querySelector('[data-onay-baslik]');
            var metin = onayModal.querySelector('[data-onay-metin]');
            var buton = onayModal.querySelector('[data-onay-buton]');

            if (baslik) baslik.textContent = kaynakForm.dataset.onayBaslik || 'Emin misiniz?';
            if (metin) metin.textContent = kaynakForm.dataset.onayMetin || '';
            if (buton) buton.textContent = kaynakForm.dataset.onayButon || 'Evet, devam et';

            modalAc(onayModal);
        });
    });

    /* ----------------------------------------------------------------------
     * Randevu detay modalı
     * -------------------------------------------------------------------- */
    var detayModal = doc.getElementById('detayModal');

    doc.querySelectorAll('[data-detay-ac]').forEach(function (buton) {
        buton.addEventListener('click', function () {
            var satir = buton.closest('[data-detay]');
            if (!satir || !detayModal) {
                return;
            }

            var veri;
            try {
                veri = JSON.parse(satir.getAttribute('data-detay'));
            } catch (hata) {
                return;
            }

            var avatar = detayModal.querySelector('[data-detay-avatar]');
            var durum = detayModal.querySelector('[data-detay-durum]');

            if (avatar) {
                avatar.textContent = veri.avatar || '--';
                avatar.className = 'avatar avatar--lg tone-' + (veri.tone || 'teal');
            }

            if (durum) {
                durum.textContent = veri.durum || '—';
                durum.className = 'status-badge tone-' + (veri.durum_renk || 'teal');
            }

            detayYaz(detayModal, '[data-detay-ad]', veri.ad);
            detayYaz(detayModal, '[data-detay-telefon]', veri.telefon);
            detayYaz(detayModal, '[data-detay-hizmet]', veri.hizmet);
            detayYaz(detayModal, '[data-detay-fiyat]', veri.ucret);
            detayYaz(detayModal, '[data-detay-tarih]', veri.tarih);
            detayYaz(detayModal, '[data-detay-saat]', veri.saat);
            detayYaz(detayModal, '[data-detay-personel]', veri.personel);
            detayYaz(detayModal, '[data-detay-sure]', veri.sure);
            detayYaz(detayModal, '[data-detay-eposta]', veri.eposta);
            detayYaz(detayModal, '[data-detay-not]', veri.not);

            modalAc(detayModal);
        });
    });

    function detayYaz(kapsam, secici, deger) {
        var el = kapsam.querySelector(secici);
        if (el) {
            el.textContent = deger || '—';
        }
    }

    /* ----------------------------------------------------------------------
     * Yeni randevu formu — canlı özet
     * -------------------------------------------------------------------- */
    var form = doc.querySelector('[data-booking-form]');

    if (form) {
        var ozetServis = form.parentElement.querySelector('[data-summary-service]');
        var ozetMeta = form.parentElement.querySelector('[data-summary-meta]');
        var ozetAd = form.parentElement.querySelector('[data-summary-name]');
        var ozetTarih = form.parentElement.querySelector('[data-summary-date]');
        var ozetSaat = form.parentElement.querySelector('[data-summary-time]');
        var ozetPersonel = form.parentElement.querySelector('[data-summary-staff]');
        var ozetSure = form.parentElement.querySelector('[data-summary-duration]');
        var ozetFiyat = form.parentElement.querySelector('[data-summary-price]');
        var ozetIkon = form.parentElement.querySelector('[data-summary-icon]');

        var tarihGirdisi = form.querySelector('[data-booking-date]');
        var personelSecimi = form.querySelector('[data-booking-staff]');
        var adGirdisi = form.querySelector('#adSoyad');
        var notAlani = form.querySelector('[data-not-area]');
        var sayac = form.querySelector('[data-char-count]');
        var saatGirdileri = Array.prototype.slice.call(form.querySelectorAll('input[name="saat"]'));

        var personelGruplari = {};

        if (personelSecimi) {
            Array.prototype.forEach.call(personelSecimi.querySelectorAll('optgroup'), function (grup) {
                var doktorlar = Array.prototype.map.call(grup.querySelectorAll('option'), function (secenek) {
                    return {
                        deger: secenek.value,
                        metin: secenek.textContent.trim(),
                        zam: Number(secenek.dataset.zam || 0)
                    };
                });

                personelGruplari[grup.getAttribute('data-hizmet') || ''] = {
                    etiket: grup.label,
                    doktorlar: doktorlar
                };
            });
        }

        var paraBicimi = new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 });
        var tarihBicimi = new Intl.DateTimeFormat('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });

        function seciliHizmet() {
            return form.querySelector('input[name="hizmet"]:checked');
        }

        function seciliSaat() {
            var secili = form.querySelector('input[name="saat"]:checked');
            return secili ? secili.value : '';
        }

        function bugunISO() {
            var simdi = new Date();
            return simdi.getFullYear() + '-' +
                String(simdi.getMonth() + 1).padStart(2, '0') + '-' +
                String(simdi.getDate()).padStart(2, '0');
        }

        function tarihiYaz(deger) {
            if (!deger) {
                return 'Tarih seçin';
            }

            var parcalar = deger.split('-');
            if (parcalar.length !== 3) {
                return deger;
            }

            var tarih = new Date(Number(parcalar[0]), Number(parcalar[1]) - 1, Number(parcalar[2]));
            return tarihBicimi.format(tarih);
        }

        function personelSecenekleriniGuncelle() {
            var hizmet = seciliHizmet();

            if (!personelSecimi || !hizmet) {
                return;
            }

            var hizmetAnahtari = hizmet.value;
            var oncekiSecim = personelSecimi.value;
            var grup = personelGruplari[hizmetAnahtari];

            personelSecimi.innerHTML = '';

            var farketmez = document.createElement('option');
            farketmez.value = 'farketmez';
            farketmez.textContent = 'Fark etmez';
            farketmez.dataset.zam = '0';
            personelSecimi.appendChild(farketmez);

            if (grup && grup.doktorlar.length) {
                var optgroup = document.createElement('optgroup');
                optgroup.label = grup.etiket;

                grup.doktorlar.forEach(function (doktor) {
                    var secenek = document.createElement('option');
                    secenek.value = doktor.deger;
                    secenek.textContent = doktor.metin;
                    secenek.dataset.zam = String(doktor.zam || 0);
                    optgroup.appendChild(secenek);
                });

                personelSecimi.appendChild(optgroup);
            }

            var secimGecerli = Array.prototype.some.call(personelSecimi.options, function (secenek) {
                return secenek.value === oncekiSecim;
            });

            personelSecimi.value = secimGecerli ? oncekiSecim : 'farketmez';
        }

        function ozetiGuncelle() {
            var hizmet = seciliHizmet();
            var saat = seciliSaat();
            var tarih = tarihGirdisi ? tarihGirdisi.value : '';
            var ad = adGirdisi ? adGirdisi.value.trim() : '';
            var personel = personelSecimi ? personelSecimi.options[personelSecimi.selectedIndex] : null;

            if (hizmet) {
                var adi = hizmet.dataset.ad || 'Poliklinik';
                var sure = Number(hizmet.dataset.sure || 0);
                var temelFiyat = Number(hizmet.dataset.fiyat || 0);
                var zamOrani = personel ? Number(personel.dataset.zam || 0) : 0;
                var fiyat = Math.round(temelFiyat * (1 + zamOrani / 100));
                var ikon = hizmet.dataset.ikon || 'stethoscope';

                if (ozetServis) ozetServis.textContent = adi;
                if (ozetMeta) ozetMeta.textContent = sure + ' dk · ₺' + paraBicimi.format(fiyat);
                if (ozetSure) ozetSure.textContent = sure + ' dakika';
                if (ozetFiyat) ozetFiyat.textContent = '₺' + paraBicimi.format(fiyat);
                if (ozetIkon) ozetIkon.setAttribute('href', '#icon-' + ikon);
            } else {
                if (ozetServis) ozetServis.textContent = 'Poliklinik seçin';
                if (ozetMeta) ozetMeta.textContent = 'Henüz poliklinik seçilmedi';
                if (ozetSure) ozetSure.textContent = '—';
                if (ozetFiyat) ozetFiyat.textContent = '₺0';
                if (ozetIkon) ozetIkon.setAttribute('href', '#icon-stethoscope');
            }

            if (ozetAd) ozetAd.textContent = ad || '—';
            if (ozetTarih) ozetTarih.textContent = tarihiYaz(tarih);
            if (ozetSaat) ozetSaat.textContent = saat || 'Saat seçin';
            if (ozetPersonel) ozetPersonel.textContent = personel ? personel.textContent.trim() : 'Fark etmez';
        }

        function saatleriGuncelle() {
            var tarih = tarihGirdisi ? tarihGirdisi.value : '';
            var bugun = bugunISO();
            var bugunMu = tarih === bugun;
            var simdi = new Date();
            var simdiDakika = simdi.getHours() * 60 + simdi.getMinutes();

            saatGirdileri.forEach(function (girdi) {
                var etiket = girdi.closest('.time-chip');
                var parca = (girdi.value || '00:00').split(':');
                var dakika = Number(parca[0]) * 60 + Number(parca[1]);
                var gecmis = bugunMu && dakika <= simdiDakika;

                girdi.disabled = gecmis;
                girdi.required = !gecmis;

                if (etiket) {
                    etiket.classList.toggle('is-past', gecmis);
                }

                if (gecmis && girdi.checked) {
                    girdi.checked = false;
                }
            });
        }

        function sayaciGuncelle() {
            if (notAlani && sayac) {
                sayac.textContent = notAlani.value.length + ' / 500';
            }
        }

        form.querySelectorAll('input[name="hizmet"]').forEach(function (girdi) {
            girdi.addEventListener('change', function () {
                personelSecenekleriniGuncelle();
                ozetiGuncelle();
            });
        });

        if (tarihGirdisi) {
            tarihGirdisi.addEventListener('change', function () {
                saatleriGuncelle();
                ozetiGuncelle();
            });
        }

        saatGirdileri.forEach(function (girdi) {
            girdi.addEventListener('change', ozetiGuncelle);
        });

        if (personelSecimi) {
            personelSecimi.addEventListener('change', ozetiGuncelle);
        }

        if (adGirdisi) {
            adGirdisi.addEventListener('input', ozetiGuncelle);
        }

        if (notAlani) {
            notAlani.addEventListener('input', sayaciGuncelle);
        }

        saatleriGuncelle();
        sayaciGuncelle();
        personelSecenekleriniGuncelle();
        ozetiGuncelle();
    }

    /* ----------------------------------------------------------------------
     * Görünür olunca yumuşak giriş
     * -------------------------------------------------------------------- */
    if (!azHareket && 'IntersectionObserver' in window) {
        doc.body.classList.add('reveal-ready');

        var gozlemci = new IntersectionObserver(function (girdiler) {
            girdiler.forEach(function (girdi) {
                if (girdi.isIntersecting) {
                    girdi.target.classList.add('is-visible');
                    gozlemci.unobserve(girdi.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -30px 0px'
        });

        doc.querySelectorAll('.reveal').forEach(function (el) {
            gozlemci.observe(el);
        });

        window.setTimeout(function () {
            doc.querySelectorAll('.reveal:not(.is-visible)').forEach(function (el) {
                if (el.getBoundingClientRect().top < window.innerHeight) {
                    el.classList.add('is-visible');
                }
            });
        }, 500);
    }
})();
document.addEventListener("DOMContentLoaded", function() {
    const temaButonu = document.getElementById('tema-degistir');
    const htmlEtiketi = document.documentElement;
    const moonIcon = temaButonu.querySelector('.icon-moon');
    const sunIcon = temaButonu.querySelector('.icon-sun');
    const temaText = temaButonu.querySelector('.tema-text');

    // Her yeni sekme açılışında varsayılan olarak 'light' (açık) temayı al
    const kayitliTema = sessionStorage.getItem('tema') || 'light';
    htmlEtiketi.setAttribute('data-theme', kayitliTema);
    ikonGuncelle(kayitliTema);

    temaButonu.addEventListener('click', function() {
        const mevcutTema = htmlEtiketi.getAttribute('data-theme');
        const yeniTema = mevcutTema === 'dark' ? 'light' : 'dark';
        
        htmlEtiketi.setAttribute('data-theme', yeniTema);
        // Tercihi sadece bu oturum (sekme açık kaldığı sürece) için kaydet
        sessionStorage.setItem('tema', yeniTema);
        ikonGuncelle(yeniTema);
    });

    function ikonGuncelle(tema) {
        if (tema === 'dark') {
            if(moonIcon) moonIcon.style.display = 'none';
            if(sunIcon) sunIcon.style.display = 'inline-block';
            if(temaText) temaText.textContent = 'Açık Tema';
        } else {
            if(moonIcon) moonIcon.style.display = 'inline-block';
            if(sunIcon) sunIcon.style.display = 'none';
            if(temaText) temaText.textContent = 'Koyu Tema';
        }
    }
});