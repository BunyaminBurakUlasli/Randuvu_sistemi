<?php
/**
 * YoncaHastanesi — Çekirdek yardımcılar
 * Oturum, örnek veriler, doğrulama, filtreleme ve biçimlendirme işlevleri.
 */
if (!defined('MEDIRANDEVU_BASLATILDI')) {
    define('MEDIRANDEVU_BASLATILDI', true);
}

mb_internal_encoding('UTF-8');

/* -------------------------------------------------------------------------
 * Oturum
 * ---------------------------------------------------------------------- */

/**
 * Oturumu başlatır ve ilk ziyarette örnek randevuları yükler.
 */
function oturum_hazirla(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => $params['path'] !== '' ? $params['path'] : '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    if (!isset($_SESSION['randevular']) || !is_array($_SESSION['randevular'])) {
        $_SESSION['randevular'] = varsayilan_randevular();
        $_SESSION['son_id'] = count($_SESSION['randevular']);
    }

    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']) || strlen($_SESSION['csrf']) < 32) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

/* -------------------------------------------------------------------------
 * Sabit veri kaynakları
 * ---------------------------------------------------------------------- */

/**
 * Poliklinik kataloğu.
 */
function hizmetler(): array
{
    static $hizmetler = null;

    if ($hizmetler === null) {
        $hizmetler = [
            'dahiliye' => [
                'ad'       => 'Dahiliye (İç Hastalıkları)',
                'kisa'     => 'Dahiliye',
                'sure'     => 20,
                'fiyat'    => 450,
                'aciklama' => 'Genel sağlık kontrolü, iç hastalıkları muayenesi ve kronik hastalık takibi.',
                'ikon'     => 'stethoscope',
                'renk'     => 'teal',
            ],
            'kardiyoloji' => [
                'ad'       => 'Kardiyoloji',
                'kisa'     => 'Kardiyoloji',
                'sure'     => 25,
                'fiyat'    => 650,
                'aciklama' => 'Kalp ve damar sağlığı değerlendirmesi, tansiyon ve ritim takibi.',
                'ikon'     => 'heart-pulse',
                'renk'     => 'blue',
            ],
            'ortopedi' => [
                'ad'       => 'Ortopedi ve Travmatoloji',
                'kisa'     => 'Ortopedi',
                'sure'     => 25,
                'fiyat'    => 600,
                'aciklama' => 'Kas, iskelet ve eklem ağrıları için değerlendirme ve tedavi planı.',
                'ikon'     => 'bone',
                'renk'     => 'amber',
            ],
            'goz' => [
                'ad'       => 'Göz Hastalıkları',
                'kisa'     => 'Göz',
                'sure'     => 20,
                'fiyat'    => 500,
                'aciklama' => 'Görme kontrolü, göz muayenesi ve görme keskinliği değerlendirmesi.',
                'ikon'     => 'eye',
                'renk'     => 'mint',
            ],
            'dis' => [
                'ad'       => 'Diş Hekimliği',
                'kisa'     => 'Diş',
                'sure'     => 30,
                'fiyat'    => 550,
                'aciklama' => 'Ağız ve diş sağlığı muayenesi, kontrol ve koruyucu tedavi planlaması.',
                'ikon'     => 'tooth',
                'renk'     => 'violet',
            ],
            'dermatoloji' => [
                'ad'       => 'Dermatoloji',
                'kisa'     => 'Dermatoloji',
                'sure'     => 20,
                'fiyat'    => 500,
                'aciklama' => 'Cilt, saç ve tırnak sağlığı için hekim değerlendirmesi.',
                'ikon'     => 'skin',
                'renk'     => 'rose',
            ],
            'cocuk' => [
                'ad'       => 'Çocuk Sağlığı ve Hastalıkları',
                'kisa'     => 'Çocuk Sağlığı',
                'sure'     => 20,
                'fiyat'    => 450,
                'aciklama' => 'Bebek, çocuk ve ergen sağlığı için rutin kontrol ve gelişim takibi.',
                'ikon'     => 'baby',
                'renk'     => 'mint',
            ],
            'kbb' => [
                'ad'       => 'Kulak Burun Boğaz',
                'kisa'     => 'KBB',
                'sure'     => 20,
                'fiyat'    => 500,
                'aciklama' => 'Kulak, burun ve boğaz şikâyetleri için muayene ve değerlendirme.',
                'ikon'     => 'ear',
                'renk'     => 'indigo',
            ],
            'noroloji' => [
                'ad'       => 'Nöroloji',
                'kisa'     => 'Nöroloji',
                'sure'     => 25,
                'fiyat'    => 600,
                'aciklama' => 'Baş ağrısı, sinir sistemi ve nörolojik şikâyetler için değerlendirme.',
                'ikon'     => 'brain',
                'renk'     => 'violet',
            ],
            'uroloji' => [
                'ad'       => 'Üroloji',
                'kisa'     => 'Üroloji',
                'sure'     => 20,
                'fiyat'    => 550,
                'aciklama' => 'Böbrek, idrar yolları ve ürolojik sağlık için muayene ve takip.',
                'ikon'     => 'kidney',
                'renk'     => 'blue',
            ],
            'kadin-dogum' => [
                'ad'       => 'Kadın Hastalıkları ve Doğum',
                'kisa'     => 'Kadın Doğum',
                'sure'     => 25,
                'fiyat'    => 650,
                'aciklama' => 'Kadın sağlığı, gebelik takibi ve jinekolojik değerlendirme.',
                'ikon'     => 'female',
                'renk'     => 'rose',
            ],
            'fizik-tedavi' => [
                'ad'       => 'Fizik Tedavi ve Rehabilitasyon',
                'kisa'     => 'Fizik Tedavi',
                'sure'     => 30,
                'fiyat'    => 600,
                'aciklama' => 'Kas-iskelet ağrıları, hareket kısıtlılığı ve rehabilitasyon programları.',
                'ikon'     => 'activity',
                'renk'     => 'amber',
            ],
            'gogus' => [
                'ad'       => 'Göğüs Hastalıkları',
                'kisa'     => 'Göğüs',
                'sure'     => 20,
                'fiyat'    => 550,
                'aciklama' => 'Solunum yolu şikâyetleri, astım ve akciğer sağlığı değerlendirmesi.',
                'ikon'     => 'lungs',
                'renk'     => 'teal',
            ],
            'psikiyatri' => [
                'ad'       => 'Ruh Sağlığı ve Psikiyatri',
                'kisa'     => 'Psikiyatri',
                'sure'     => 40,
                'fiyat'    => 700,
                'aciklama' => 'Kaygı, depresyon ve ruh sağlığı için uzman hekim değerlendirmesi.',
                'ikon'     => 'psychology',
                'renk'     => 'indigo',
            ],
        ];
    }

    return $hizmetler;
}

/**
 * Randevu durumları.
 */
function durumlar(): array
{
    static $durumlar = null;

    if ($durumlar === null) {
        $durumlar = [
            'bekliyor' => [
                'ad'   => 'Bekliyor',
                'renk' => 'amber',
                'dot'  => '#d97706',
                'ikon' => 'clock',
            ],
            'onaylandi' => [
                'ad'   => 'Onaylandı',
                'renk' => 'teal',
                'dot'  => '#0d9488',
                'ikon' => 'check-circle',
            ],
            'tamamlandi' => [
                'ad'   => 'Tamamlandı',
                'renk' => 'blue',
                'dot'  => '#0284c7',
                'ikon' => 'check',
            ],
            'iptal' => [
                'ad'   => 'İptal',
                'renk' => 'coral',
                'dot'  => '#e11d48',
                'ikon' => 'close',
            ],
        ];
    }

    return $durumlar;
}

/**
 * Doktor listesi.
 * Her poliklinikte en az üç hekim bulunur: Op. Dr., Doç. Dr. ve Prof. Dr.
 */
function personeller(): array
{
    static $personeller = null;

    if ($personeller === null) {
        $personeller = [
            'farketmez' => ['ad' => 'Fark etmez', 'rol' => 'Doktor fark etmez'],

            // Dahiliye
            'ayse'            => ['ad' => 'Op. Dr. Ayşe Kılıç', 'rol' => 'Dahiliye', 'hizmet' => 'dahiliye'],
            'dahiliye-doc'    => ['ad' => 'Doç. Dr. Murat Özkan', 'rol' => 'Dahiliye', 'hizmet' => 'dahiliye'],
            'dahiliye-prof'   => ['ad' => 'Prof. Dr. Fatma Yıldırım', 'rol' => 'Dahiliye', 'hizmet' => 'dahiliye'],

            // Kardiyoloji
            'burak'           => ['ad' => 'Op. Dr. Burak Erdem', 'rol' => 'Kardiyoloji', 'hizmet' => 'kardiyoloji'],
            'kardiyoloji-doc' => ['ad' => 'Doç. Dr. Elif Kaplan', 'rol' => 'Kardiyoloji', 'hizmet' => 'kardiyoloji'],
            'kardiyoloji-prof'=> ['ad' => 'Prof. Dr. Ahmet Tunç', 'rol' => 'Kardiyoloji', 'hizmet' => 'kardiyoloji'],

            // Ortopedi ve Travmatoloji
            'selin'           => ['ad' => 'Op. Dr. Selin Acar', 'rol' => 'Ortopedi', 'hizmet' => 'ortopedi'],
            'ortopedi-doc'    => ['ad' => 'Doç. Dr. Kerem Yalçın', 'rol' => 'Ortopedi', 'hizmet' => 'ortopedi'],
            'ortopedi-prof'   => ['ad' => 'Prof. Dr. Hakan Demir', 'rol' => 'Ortopedi', 'hizmet' => 'ortopedi'],

            // Göz Hastalıkları
            'emre'            => ['ad' => 'Op. Dr. Emre Şahin', 'rol' => 'Göz Hastalıkları', 'hizmet' => 'goz'],
            'goz-doc'         => ['ad' => 'Doç. Dr. Zeynep Arslan', 'rol' => 'Göz Hastalıkları', 'hizmet' => 'goz'],
            'goz-prof'        => ['ad' => 'Prof. Dr. Nihat Korkmaz', 'rol' => 'Göz Hastalıkları', 'hizmet' => 'goz'],

            // Diş Hekimliği
            'merve'           => ['ad' => 'Op. Dr. Merve Aydın', 'rol' => 'Diş Hekimliği', 'hizmet' => 'dis'],
            'dis-doc'         => ['ad' => 'Doç. Dr. Sinan Bulut', 'rol' => 'Diş Hekimliği', 'hizmet' => 'dis'],
            'dis-prof'        => ['ad' => 'Prof. Dr. Aylin Çetin', 'rol' => 'Diş Hekimliği', 'hizmet' => 'dis'],

            // Dermatoloji
            'onur'            => ['ad' => 'Op. Dr. Onur Yalçın', 'rol' => 'Dermatoloji', 'hizmet' => 'dermatoloji'],
            'dermatoloji-doc' => ['ad' => 'Doç. Dr. Derya Şen', 'rol' => 'Dermatoloji', 'hizmet' => 'dermatoloji'],
            'dermatoloji-prof'=> ['ad' => 'Prof. Dr. Levent Aksoy', 'rol' => 'Dermatoloji', 'hizmet' => 'dermatoloji'],

            // Çocuk Sağlığı ve Hastalıkları
            'deniz'           => ['ad' => 'Op. Dr. Deniz Koç', 'rol' => 'Çocuk Sağlığı', 'hizmet' => 'cocuk'],
            'cocuk-doc'       => ['ad' => 'Doç. Dr. Pınar Güneş', 'rol' => 'Çocuk Sağlığı', 'hizmet' => 'cocuk'],
            'cocuk-prof'      => ['ad' => 'Prof. Dr. Serkan Polat', 'rol' => 'Çocuk Sağlığı', 'hizmet' => 'cocuk'],

            // Kulak Burun Boğaz
            'ceren'           => ['ad' => 'Op. Dr. Ceren Aksoy', 'rol' => 'KBB', 'hizmet' => 'kbb'],
            'kbb-doc'         => ['ad' => 'Doç. Dr. Barış Ünal', 'rol' => 'KBB', 'hizmet' => 'kbb'],
            'kbb-prof'        => ['ad' => 'Prof. Dr. Nihal Erdoğan', 'rol' => 'KBB', 'hizmet' => 'kbb'],

            // Nöroloji
            'noroloji-op'     => ['ad' => 'Op. Dr. Gizem Kurt', 'rol' => 'Nöroloji', 'hizmet' => 'noroloji'],
            'noroloji-doc'    => ['ad' => 'Doç. Dr. Tolga Bayram', 'rol' => 'Nöroloji', 'hizmet' => 'noroloji'],
            'noroloji-prof'   => ['ad' => 'Prof. Dr. Aysel Doğan', 'rol' => 'Nöroloji', 'hizmet' => 'noroloji'],

            // Üroloji
            'uroloji-op'      => ['ad' => 'Op. Dr. Umut Şimşek', 'rol' => 'Üroloji', 'hizmet' => 'uroloji'],
            'uroloji-doc'     => ['ad' => 'Doç. Dr. Cem Aslan', 'rol' => 'Üroloji', 'hizmet' => 'uroloji'],
            'uroloji-prof'    => ['ad' => 'Prof. Dr. İbrahim Koçak', 'rol' => 'Üroloji', 'hizmet' => 'uroloji'],

            // Kadın Hastalıkları ve Doğum
            'kadin-dogum-op'  => ['ad' => 'Op. Dr. Esra Yavuz', 'rol' => 'Kadın Doğum', 'hizmet' => 'kadin-dogum'],
            'kadin-dogum-doc' => ['ad' => 'Doç. Dr. Gülşen Aktaş', 'rol' => 'Kadın Doğum', 'hizmet' => 'kadin-dogum'],
            'kadin-dogum-prof'=> ['ad' => 'Prof. Dr. Hüseyin Özdemir', 'rol' => 'Kadın Doğum', 'hizmet' => 'kadin-dogum'],

            // Fizik Tedavi ve Rehabilitasyon
            'fizik-tedavi-op' => ['ad' => 'Op. Dr. Seda Kaya', 'rol' => 'Fizik Tedavi', 'hizmet' => 'fizik-tedavi'],
            'fizik-tedavi-doc'=> ['ad' => 'Doç. Dr. Okan Yücel', 'rol' => 'Fizik Tedavi', 'hizmet' => 'fizik-tedavi'],
            'fizik-tedavi-prof'=> ['ad' => 'Prof. Dr. Nurten Aydın', 'rol' => 'Fizik Tedavi', 'hizmet' => 'fizik-tedavi'],

            // Göğüs Hastalıkları
            'gogus-op'        => ['ad' => 'Op. Dr. Bora Tekin', 'rol' => 'Göğüs', 'hizmet' => 'gogus'],
            'gogus-doc'       => ['ad' => 'Doç. Dr. Sevilay Çakır', 'rol' => 'Göğüs', 'hizmet' => 'gogus'],
            'gogus-prof'      => ['ad' => 'Prof. Dr. Mehmet Ali Kılıç', 'rol' => 'Göğüs', 'hizmet' => 'gogus'],

            // Ruh Sağlığı ve Psikiyatri
            'psikiyatri-op'   => ['ad' => 'Op. Dr. İrem Solak', 'rol' => 'Psikiyatri', 'hizmet' => 'psikiyatri'],
            'psikiyatri-doc'  => ['ad' => 'Doç. Dr. Kaan Ersoy', 'rol' => 'Psikiyatri', 'hizmet' => 'psikiyatri'],
            'psikiyatri-prof' => ['ad' => 'Prof. Dr. Filiz Tanrıverdi', 'rol' => 'Psikiyatri', 'hizmet' => 'psikiyatri'],
        ];
    }

    return $personeller;
}

/**
 * Belirli bir poliklinikte görevli doktorları döndürür.
 *
 * @return array<string, array{ad: string, rol: string, hizmet: string}>
 */
function poliklinik_doktorlari(string $hizmet): array
{
    $sonuc = [];

    foreach (personeller() as $anahtar => $personel) {
        if ($anahtar === 'farketmez') {
            continue;
        }

        if ((string) ($personel['hizmet'] ?? '') === $hizmet) {
            $sonuc[$anahtar] = $personel;
        }
    }

    return $sonuc;
}

/**
 * 08:30 – 17:30 arası 30 dakikalık saat dilimleri.
 */
function saat_dilimleri(): array
{
    static $dilimler = null;

    if ($dilimler === null) {
        $dilimler = [];
        for ($dakika = 8 * 60 + 30; $dakika <= 17 * 60 + 30; $dakika += 30) {
            $dilimler[] = sprintf('%02d:%02d', intdiv($dakika, 60), $dakika % 60);
        }
    }

    return $dilimler;
}

/**
 * İlk açılışta yüklenen örnek randevular.
 */
function varsayilan_randevular(): array
{
    $gun = static function (int $offset): string {
        return date('Y-m-d', strtotime(($offset >= 0 ? '+' : '') . $offset . ' day'));
    };

    $kayitlar = [
        [
            'ad_soyad' => 'Elif Yılmaz',
            'telefon'  => '0532 418 72 90',
            'eposta'   => 'elif.yilmaz@ornek.com',
            'hizmet'   => 'dahiliye',
            'tarih'    => $gun(0),
            'saat'     => '10:00',
            'personel' => 'ayse',
            'durum'    => 'onaylandi',
            'not'      => 'Tansiyon ve şeker takibi için kontrol randevusu.',
        ],
        [
            'ad_soyad' => 'Mert Kaya',
            'telefon'  => '0541 226 90 31',
            'eposta'   => 'mert.kaya@ornek.com',
            'hizmet'   => 'kardiyoloji',
            'tarih'    => $gun(0),
            'saat'     => '11:30',
            'personel' => 'burak',
            'durum'    => 'bekliyor',
            'not'      => 'Efor sırasında çarpıntı şikâyeti var.',
        ],
        [
            'ad_soyad' => 'Zeynep Demir',
            'telefon'  => '0555 903 14 27',
            'eposta'   => 'zeynep.demir@ornek.com',
            'hizmet'   => 'dermatoloji',
            'tarih'    => $gun(0),
            'saat'     => '14:00',
            'personel' => 'onur',
            'durum'    => 'onaylandi',
            'not'      => 'Ciltte kaşıntı ve kuruluk şikâyeti bulunuyor.',
        ],
        [
            'ad_soyad' => 'Can Aydın',
            'telefon'  => '0533 771 08 64',
            'eposta'   => 'can.aydin@ornek.com',
            'hizmet'   => 'ortopedi',
            'tarih'    => $gun(0),
            'saat'     => '16:30',
            'personel' => 'selin',
            'durum'    => 'bekliyor',
            'not'      => 'Sol dizde ağrı; merdiven inerken artıyor.',
        ],
        [
            'ad_soyad' => 'Selin Arslan',
            'telefon'  => '0507 342 65 18',
            'eposta'   => 'selin.arslan@ornek.com',
            'hizmet'   => 'goz',
            'tarih'    => $gun(1),
            'saat'     => '09:30',
            'personel' => 'emre',
            'durum'    => 'onaylandi',
            'not'      => 'Görme bulanıklığı; gözlük numarası kontrolü yapılacak.',
        ],
        [
            'ad_soyad' => 'Burak Şahin',
            'telefon'  => '0538 610 49 52',
            'eposta'   => 'burak.sahin@ornek.com',
            'hizmet'   => 'dis',
            'tarih'    => $gun(1),
            'saat'     => '12:00',
            'personel' => 'merve',
            'durum'    => 'bekliyor',
            'not'      => 'Diş eti hassasiyeti ve rutin kontrol muayenesi.',
        ],
        [
            'ad_soyad' => 'Deniz Koç',
            'telefon'  => '0546 285 77 03',
            'eposta'   => 'deniz.koc@ornek.com',
            'hizmet'   => 'cocuk',
            'tarih'    => $gun(2),
            'saat'     => '15:00',
            'personel' => 'deniz',
            'durum'    => 'onaylandi',
            'not'      => 'Çocuk hastanın rutin gelişim kontrolü.',
        ],
        [
            'ad_soyad' => 'Ece Yıldız',
            'telefon'  => '0552 114 36 80',
            'eposta'   => 'ece.yildiz@ornek.com',
            'hizmet'   => 'kbb',
            'tarih'    => $gun(3),
            'saat'     => '11:00',
            'personel' => 'ceren',
            'durum'    => 'bekliyor',
            'not'      => 'Tekrarlayan boğaz ağrısı ve burun tıkanıklığı.',
        ],
        [
            'ad_soyad' => 'Ahmet Çelik',
            'telefon'  => '0530 457 91 26',
            'eposta'   => 'ahmet.celik@ornek.com',
            'hizmet'   => 'dahiliye',
            'tarih'    => $gun(-1),
            'saat'     => '16:00',
            'personel' => 'ayse',
            'durum'    => 'tamamlandi',
            'not'      => 'Yıllık genel sağlık kontrolü tamamlandı.',
        ],
        [
            'ad_soyad' => 'Melis Tunç',
            'telefon'  => '0544 802 13 59',
            'eposta'   => 'melis.tunc@ornek.com',
            'hizmet'   => 'kardiyoloji',
            'tarih'    => $gun(-3),
            'saat'     => '13:00',
            'personel' => 'burak',
            'durum'    => 'iptal',
            'not'      => 'Hasta iş programı nedeniyle randevuyu iptal etti.',
        ],
    ];

    $sonuc = [];
    $simdi = time();

    foreach ($kayitlar as $sira => $kayit) {
        $kayit['id'] = $sira + 1;
        $kayit['olusturma'] = $simdi - (count($kayitlar) - $sira) * 5400;
        $sonuc[] = $kayit;
    }

    return $sonuc;
}

/* -------------------------------------------------------------------------
 * Veri erişimi
 * ---------------------------------------------------------------------- */

function randevulari_getir(): array
{
    oturum_hazirla();

    return is_array($_SESSION['randevular']) ? $_SESSION['randevular'] : [];
}

function randevu_ekle(array $veri): int
{
    oturum_hazirla();

    $yeniId = (int) ($_SESSION['son_id'] ?? 0) + 1;

    $kayit = [
        'id'         => $yeniId,
        'ad_soyad'   => (string) ($veri['ad_soyad'] ?? ''),
        'telefon'    => (string) ($veri['telefon'] ?? ''),
        'eposta'     => (string) ($veri['eposta'] ?? ''),
        'hizmet'     => (string) ($veri['hizmet'] ?? ''),
        'tarih'      => (string) ($veri['tarih'] ?? ''),
        'saat'       => (string) ($veri['saat'] ?? ''),
        'personel'   => (string) ($veri['personel'] ?? 'farketmez'),
        'durum'      => 'bekliyor',
        'not'        => (string) ($veri['not'] ?? ''),
        'olusturma'  => time(),
    ];

    $_SESSION['randevular'][] = $kayit;
    $_SESSION['son_id'] = $yeniId;

    return $yeniId;
}

function randevu_sil(int $id): bool
{
    oturum_hazirla();

    $kalanlar = [];
    $silindi = false;

    foreach ($_SESSION['randevular'] as $randevu) {
        if ((int) ($randevu['id'] ?? 0) === $id) {
            $silindi = true;
            continue;
        }
        $kalanlar[] = $randevu;
    }

    if ($silindi) {
        $_SESSION['randevular'] = $kalanlar;
    }

    return $silindi;
}

function randevu_durum_guncelle(int $id, string $yeniDurum): bool
{
    oturum_hazirla();

    if (!isset(durumlar()[$yeniDurum])) {
        return false;
    }

    foreach ($_SESSION['randevular'] as $indeks => $randevu) {
        if ((int) ($randevu['id'] ?? 0) === $id) {
            $_SESSION['randevular'][$indeks]['durum'] = $yeniDurum;
            return true;
        }
    }

    return false;
}

/* -------------------------------------------------------------------------
 * Bilgi yardımcıları
 * ---------------------------------------------------------------------- */

function hizmet_bilgisi(string $anahtar): array
{
    $hizmetler = hizmetler();

    return $hizmetler[$anahtar] ?? [
        'ad'       => 'Bilinmeyen poliklinik',
        'kisa'     => 'Poliklinik',
        'sure'     => 20,
        'fiyat'    => 0,
        'aciklama' => '',
        'ikon'     => 'stethoscope',
        'renk'     => 'teal',
    ];
}

function durum_bilgisi(string $anahtar): array
{
    $durumlar = durumlar();

    return $durumlar[$anahtar] ?? [
        'ad'   => 'Bilinmiyor',
        'renk' => 'teal',
        'dot'  => '#0f766e',
        'ikon' => 'alert',
    ];
}

function personel_bilgisi(string $anahtar): array
{
    $personeller = personeller();

    return $personeller[$anahtar] ?? $personeller['farketmez'];
}

/**
 * Doktor unvanına göre ücret artış oranını (%) döndürür.
 * Op. Dr. taban ücret, Doç. Dr. +%25, Prof. Dr. +%50.
 */
function doktor_zam_orani(string $personel): int
{
    $doktor = personel_bilgisi($personel);
    $ad = (string) ($doktor['ad'] ?? '');

    if (str_starts_with($ad, 'Prof. Dr.')) {
        return 50;
    }

    if (str_starts_with($ad, 'Doç. Dr.')) {
        return 25;
    }

    return 0;
}

/**
 * Poliklinik taban ücretine seçilen doktorun unvan farkını uygular.
 */
function doktor_ucreti(string $hizmet, string $personel = 'farketmez'): int
{
    $taban = (int) hizmet_bilgisi($hizmet)['fiyat'];
    $oran = doktor_zam_orani($personel);

    return (int) round($taban * (1 + $oran / 100));
}

/* -------------------------------------------------------------------------
 * Biçimlendirme
 * ---------------------------------------------------------------------- */

function e(mixed $deger): string
{
    return htmlspecialchars((string) $deger, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function para_formatla(mixed $tutar): string
{
    return '₺' . number_format((float) $tutar, 0, ',', '.');
}

function turkce_tarih(string $tarih, bool $uzun = false): string
{
    $zaman = strtotime($tarih);

    if ($zaman === false) {
        return $tarih;
    }

    $aylar = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
    ];

    $gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];

    $metin = (int) date('j', $zaman) . ' ' . $aylar[(int) date('n', $zaman)] . ' ' . date('Y', $zaman);

    if ($uzun) {
        $metin .= ', ' . $gunler[(int) date('w', $zaman)];
    }

    return $metin;
}

function gun_etiketi(string $tarih): string
{
    $bugun = date('Y-m-d');
    $yarin = date('Y-m-d', strtotime('+1 day'));
    $dun = date('Y-m-d', strtotime('-1 day'));

    if ($tarih === $bugun) {
        return 'Bugün';
    }

    if ($tarih === $yarin) {
        return 'Yarın';
    }

    if ($tarih === $dun) {
        return 'Dün';
    }

    $zaman = strtotime($tarih);
    if ($zaman === false) {
        return $tarih;
    }

    $gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];

    return $gunler[(int) date('w', $zaman)];
}

function bas_harfler(string $ad): string
{
    $ad = trim(preg_replace('/\s+/u', ' ', $ad) ?? $ad);
    $parcalar = preg_split('/\s+/u', $ad) ?: [];
    $harfler = '';

    foreach (array_slice($parcalar, 0, 2) as $parca) {
        if ($parca === '') {
            continue;
        }
        $harf = mb_substr($parca, 0, 1, 'UTF-8');
        $harf = strtr($harf, [
            'i' => 'İ', 'ı' => 'I', 'ş' => 'Ş', 'ç' => 'Ç',
            'ğ' => 'Ğ', 'ü' => 'Ü', 'ö' => 'Ö',
        ]);
        $harfler .= mb_strtoupper($harf, 'UTF-8');
    }

    return $harfler !== '' ? $harfler : '?';
}

function avatar_rengi(string $ad): string
{
    $tonlar = ['violet', 'rose', 'amber', 'teal', 'blue', 'coral', 'indigo', 'mint'];
    $ozet = abs((int) crc32(mb_strtolower(trim($ad), 'UTF-8')));

    return $tonlar[$ozet % count($tonlar)];
}

/* -------------------------------------------------------------------------
 * Filtreleme, arama ve sıralama
 * ---------------------------------------------------------------------- */

function mevcut_filtreler(): array
{
    $hizmetler = hizmetler();
    $durumlar = durumlar();

    $q = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($q, 'UTF-8') > 80) {
        $q = mb_substr($q, 0, 80, 'UTF-8');
    }

    $hizmet = (string) ($_GET['hizmet'] ?? '');
    if (!isset($hizmetler[$hizmet])) {
        $hizmet = '';
    }

    $durum = (string) ($_GET['durum'] ?? '');
    if (!isset($durumlar[$durum])) {
        $durum = '';
    }

    $tarih = (string) ($_GET['tarih'] ?? '');
    if (!tarih_gecerli_mi($tarih)) {
        $tarih = '';
    }

    $sirala = (string) ($_GET['sirala'] ?? 'akilli');
    $izinliSiralama = ['akilli', 'tarih-artan', 'tarih-azalan', 'isim', 'yeni'];
    if (!in_array($sirala, $izinliSiralama, true)) {
        $sirala = 'akilli';
    }

    $takvim = (string) ($_GET['takvim'] ?? '');
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $takvim)) {
        $takvim = '';
    }

    return [
        'q'       => $q,
        'hizmet'  => $hizmet,
        'durum'   => $durum,
        'tarih'   => $tarih,
        'sirala'  => $sirala,
        'takvim'  => $takvim,
    ];
}

function filtre_aktif_mi(array $filtreler): bool
{
    return $filtreler['q'] !== ''
        || $filtreler['hizmet'] !== ''
        || $filtreler['durum'] !== ''
        || $filtreler['tarih'] !== '';
}

function sorgu_olustur(array $filtreler): string
{
    $parametreler = [];

    foreach (['q', 'hizmet', 'durum', 'tarih'] as $anahtar) {
        if (($filtreler[$anahtar] ?? '') !== '') {
            $parametreler[$anahtar] = $filtreler[$anahtar];
        }
    }

    if (($filtreler['sirala'] ?? 'akilli') !== 'akilli') {
        $parametreler['sirala'] = $filtreler['sirala'];
    }

    if (($filtreler['takvim'] ?? '') !== '') {
        $parametreler['takvim'] = $filtreler['takvim'];
    }

    return http_build_query($parametreler);
}

function filtre_url(array $degisiklikler = [], array $kaldir = []): string
{
    $filtreler = mevcut_filtreler();

    foreach ($kaldir as $anahtar) {
        $filtreler[$anahtar] = '';
    }

    foreach ($degisiklikler as $anahtar => $deger) {
        $filtreler[$anahtar] = $deger;
    }

    $sorgu = sorgu_olustur($filtreler);

    return 'index.php' . ($sorgu !== '' ? '?' . $sorgu : '');
}

function filtrele_ve_sirala(array $randevular, array $filtreler): array
{
    $q = trim((string) ($filtreler['q'] ?? ''));
    $hizmet = (string) ($filtreler['hizmet'] ?? '');
    $durum = (string) ($filtreler['durum'] ?? '');
    $tarih = (string) ($filtreler['tarih'] ?? '');

    $sonuc = [];

    foreach ($randevular as $randevu) {
        if ($hizmet !== '' && (string) ($randevu['hizmet'] ?? '') !== $hizmet) {
            continue;
        }

        if ($durum !== '' && (string) ($randevu['durum'] ?? '') !== $durum) {
            continue;
        }

        if ($tarih !== '' && (string) ($randevu['tarih'] ?? '') !== $tarih) {
            continue;
        }

        if ($q !== '') {
            $hizmetBilgi = hizmet_bilgisi((string) ($randevu['hizmet'] ?? ''));
            $personelBilgi = personel_bilgisi((string) ($randevu['personel'] ?? ''));
            $aranan = implode(' ', [
                (string) ($randevu['ad_soyad'] ?? ''),
                (string) ($randevu['telefon'] ?? ''),
                (string) ($randevu['eposta'] ?? ''),
                $hizmetBilgi['ad'],
                $hizmetBilgi['kisa'],
                $personelBilgi['ad'],
                $personelBilgi['rol'],
            ]);

            if (mb_stripos($aranan, $q, 0, 'UTF-8') === false) {
                continue;
            }
        }

        $sonuc[] = $randevu;
    }

    $sirala = (string) ($filtreler['sirala'] ?? 'akilli');

    usort($sonuc, static function (array $a, array $b) use ($sirala): int {
        $zamanA = strtotime(($a['tarih'] ?? '') . ' ' . ($a['saat'] ?? '00:00')) ?: 0;
        $zamanB = strtotime(($b['tarih'] ?? '') . ' ' . ($b['saat'] ?? '00:00')) ?: 0;

        if ($sirala === 'tarih-artan') {
            return $zamanA <=> $zamanB;
        }

        if ($sirala === 'tarih-azalan') {
            return $zamanB <=> $zamanA;
        }

        if ($sirala === 'isim') {
            return strcmp(
                mb_strtolower((string) ($a['ad_soyad'] ?? ''), 'UTF-8'),
                mb_strtolower((string) ($b['ad_soyad'] ?? ''), 'UTF-8')
            );
        }

        if ($sirala === 'yeni') {
            return ((int) ($b['olusturma'] ?? 0)) <=> ((int) ($a['olusturma'] ?? 0));
        }

        // Akıllı sıralama: iptaller en sonda, yaklaşanlar önce.
        $iptalA = ($a['durum'] ?? '') === 'iptal';
        $iptalB = ($b['durum'] ?? '') === 'iptal';

        if ($iptalA !== $iptalB) {
            return $iptalA ? 1 : -1;
        }

        $simdi = time();
        $gelecekA = $zamanA >= $simdi;
        $gelecekB = $zamanB >= $simdi;

        if ($gelecekA !== $gelecekB) {
            return $gelecekA ? -1 : 1;
        }

        return $gelecekA ? ($zamanA <=> $zamanB) : ($zamanB <=> $zamanA);
    });

    return $sonuc;
}

function bugunun_randevulari(array $randevular): array
{
    $bugun = date('Y-m-d');
    $sonuc = [];

    foreach ($randevular as $randevu) {
        if ((string) ($randevu['tarih'] ?? '') === $bugun && ($randevu['durum'] ?? '') !== 'iptal') {
            $sonuc[] = $randevu;
        }
    }

    usort($sonuc, static function (array $a, array $b): int {
        return strcmp((string) ($a['saat'] ?? ''), (string) ($b['saat'] ?? ''));
    });

    return $sonuc;
}

function istatistikler(array $randevular): array
{
    $bugun = date('Y-m-d');
    $simdi = time();

    $durumSayilari = [];
    foreach (array_keys(durumlar()) as $anahtar) {
        $durumSayilari[$anahtar] = 0;
    }

    $toplam = count($randevular);
    $bugunSayisi = 0;
    $bugunOnayli = 0;
    $bekleyen = 0;
    $ciro = 0.0;
    $sonraki = null;
    $enYakin = PHP_INT_MAX;

    foreach ($randevular as $randevu) {
        $durum = (string) ($randevu['durum'] ?? '');
        if (isset($durumSayilari[$durum])) {
            $durumSayilari[$durum]++;
        }

        $tarih = (string) ($randevu['tarih'] ?? '');
        $saat = (string) ($randevu['saat'] ?? '00:00');

        if ($durum === 'bekliyor') {
            $bekleyen++;
        }

        if ($tarih === $bugun && $durum !== 'iptal') {
            $bugunSayisi++;
            if ($durum === 'onaylandi') {
                $bugunOnayli++;
            }
        }

        if ($durum !== 'iptal') {
            $ciro += (float) doktor_ucreti(
                (string) ($randevu['hizmet'] ?? ''),
                (string) ($randevu['personel'] ?? 'farketmez')
            );
        }

        if ($durum === 'bekliyor' || $durum === 'onaylandi') {
            $zaman = strtotime($tarih . ' ' . $saat) ?: 0;
            if ($zaman >= $simdi && $zaman < $enYakin) {
                $enYakin = $zaman;
                $sonraki = $randevu;
            }
        }
    }

    $doluluk = (int) round(min(100, ($bugunSayisi / 12) * 100));

    return [
        'toplam'       => $toplam,
        'bugun'        => $bugunSayisi,
        'bugun_onayli' => $bugunOnayli,
        'bekleyen'     => $bekleyen,
        'ciro'         => $ciro,
        'doluluk'      => $doluluk,
        'durumlar'     => $durumSayilari,
        'sonraki'      => $sonraki,
    ];
}

function takvim_verisi(array $randevular, ?string $ayParam = null, ?string $seciliTarih = null): array
{
    $aylar = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
    ];

    $temelZaman = null;

    if ($ayParam !== null && preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $ayParam, $eslesme)) {
        $temelZaman = mktime(0, 0, 0, (int) $eslesme[2], 1, (int) $eslesme[1]);
    } elseif ($seciliTarih !== null && tarih_gecerli_mi($seciliTarih)) {
        $temelZaman = strtotime($seciliTarih);
    }

    if ($temelZaman === null) {
        $temelZaman = time();
    }

    $yil = (int) date('Y', $temelZaman);
    $ay = (int) date('n', $temelZaman);

    $ilkGun = mktime(0, 0, 0, $ay, 1, $yil);
    $gunSayisi = (int) date('t', $ilkGun);
    $baslangicBosluk = (int) date('N', $ilkGun) - 1;
    $hucreSayisi = (int) ceil(($baslangicBosluk + $gunSayisi) / 7) * 7;

    $sayilar = [];
    foreach ($randevular as $randevu) {
        if (($randevu['durum'] ?? '') === 'iptal') {
            continue;
        }
        $tarih = (string) ($randevu['tarih'] ?? '');
        if ($tarih !== '') {
            $sayilar[$tarih] = ($sayilar[$tarih] ?? 0) + 1;
        }
    }

    $haftalar = [];
    $hafta = [];

    for ($i = 0; $i < $hucreSayisi; $i++) {
        $zaman = $ilkGun + ($i - $baslangicBosluk) * 86400;
        $tarih = date('Y-m-d', $zaman);

        $hafta[] = [
            'tarih'   => $tarih,
            'gun'     => (int) date('j', $zaman),
            'ayda'    => (int) date('n', $zaman) === $ay,
            'bugun'   => $tarih === date('Y-m-d'),
            'secili'  => $seciliTarih !== null && $tarih === $seciliTarih,
            'sayi'    => $sayilar[$tarih] ?? 0,
        ];

        if (count($hafta) === 7) {
            $haftalar[] = $hafta;
            $hafta = [];
        }
    }

    if ($hafta !== []) {
        $haftalar[] = $hafta;
    }

    return [
        'yil'       => $yil,
        'ay'        => $ay,
        'etiket'    => $aylar[$ay] . ' ' . $yil,
        'onceki'    => date('Y-m', mktime(0, 0, 0, $ay - 1, 1, $yil)),
        'sonraki'   => date('Y-m', mktime(0, 0, 0, $ay + 1, 1, $yil)),
        'bugun_ay'  => date('Y-m'),
        'haftalar'  => $haftalar,
    ];
}

/* -------------------------------------------------------------------------
 * Doğrulama
 * ---------------------------------------------------------------------- */

function tarih_gecerli_mi(string $tarih): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
        return false;
    }

    $nesne = DateTime::createFromFormat('!Y-m-d', $tarih);

    return $nesne instanceof DateTime && $nesne->format('Y-m-d') === $tarih;
}

/**
 * Form verisini doğrular.
 *
 * @return array{veri: array, hatalar: array<string, string>}
 */
function randevu_dogrula(array $girdi, array $randevular): array
{
    $hizmetler = hizmetler();
    $personeller = personeller();

    $adSoyad = trim((string) ($girdi['ad_soyad'] ?? ''));
    $adSoyad = preg_replace('/\s+/u', ' ', $adSoyad) ?? $adSoyad;

    $telefon = trim((string) ($girdi['telefon'] ?? ''));
    $eposta = trim((string) ($girdi['eposta'] ?? ''));
    $hizmet = trim((string) ($girdi['hizmet'] ?? ''));
    $tarih = trim((string) ($girdi['tarih'] ?? ''));
    $saat = trim((string) ($girdi['saat'] ?? ''));
    $personel = trim((string) ($girdi['personel'] ?? 'farketmez'));
    $not = trim((string) ($girdi['not'] ?? ''));
    $sms = isset($girdi['sms']) && (string) $girdi['sms'] === '1';

    // Telefonu 0XXX XXX XX XX biçimine getir.
    $rakamlar = preg_replace('/\D+/', '', $telefon) ?? '';
    if (strlen($rakamlar) === 12 && str_starts_with($rakamlar, '90')) {
        $rakamlar = '0' . substr($rakamlar, 2);
    } elseif (strlen($rakamlar) === 10 && str_starts_with($rakamlar, '5')) {
        $rakamlar = '0' . $rakamlar;
    }

    if (strlen($rakamlar) === 11) {
        $telefon = substr($rakamlar, 0, 4) . ' ' . substr($rakamlar, 4, 3) . ' '
            . substr($rakamlar, 7, 2) . ' ' . substr($rakamlar, 9, 2);
    }

    if (!isset($personeller[$personel])) {
        $personel = 'farketmez';
    }

    $hatalar = [];

    if ($adSoyad === '') {
        $hatalar['ad_soyad'] = 'Ad soyad alanı zorunludur.';
    } elseif (mb_strlen($adSoyad, 'UTF-8') < 3) {
        $hatalar['ad_soyad'] = 'Ad soyad en az 3 karakter olmalıdır.';
    } elseif (mb_strlen($adSoyad, 'UTF-8') > 80) {
        $hatalar['ad_soyad'] = 'Ad soyad en fazla 80 karakter olabilir.';
    } elseif (!preg_match('/^[\p{L}\s.\'-]+$/u', $adSoyad)) {
        $hatalar['ad_soyad'] = 'Ad soyad yalnızca harf, boşluk ve . \' - karakterlerini içerebilir.';
    }

    if ($telefon === '') {
        $hatalar['telefon'] = 'Telefon numarası zorunludur.';
    } elseif (!preg_match('/^0[2-9]\d{9}$/', $rakamlar)) {
        $hatalar['telefon'] = 'Geçerli bir telefon numarası girin. Örn. 0532 418 72 90.';
    }

    if ($eposta !== '' && !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
        $hatalar['eposta'] = 'Geçerli bir e-posta adresi girin. Örn. ad@ornek.com.';
    }

    if ($hizmet === '') {
        $hatalar['hizmet'] = 'Lütfen bir poliklinik seçin.';
    } elseif (!isset($hizmetler[$hizmet])) {
        $hatalar['hizmet'] = 'Seçilen poliklinik geçersiz.';
    }

    // Seçilen doktor, poliklinikle uyuşmuyorsa "Fark etmez" olarak sıfırla.
    if ($personel !== 'farketmez' && isset($personeller[$personel])) {
        $doktorHizmet = (string) ($personeller[$personel]['hizmet'] ?? '');
        if ($doktorHizmet !== '' && $doktorHizmet !== $hizmet) {
            $personel = 'farketmez';
        }
    }

    if ($tarih === '') {
        $hatalar['tarih'] = 'Randevu tarihi zorunludur.';
    } elseif (!tarih_gecerli_mi($tarih)) {
        $hatalar['tarih'] = 'Geçerli bir tarih seçin.';
    } elseif ($tarih < date('Y-m-d')) {
        $hatalar['tarih'] = 'Geçmiş bir tarihe randevu oluşturulamaz.';
    } elseif ($tarih > date('Y-m-d', strtotime('+1 year'))) {
        $hatalar['tarih'] = 'Randevu en fazla bir yıl sonrası için oluşturulabilir.';
    }

    if ($saat === '') {
        $hatalar['saat'] = 'Lütfen bir saat dilimi seçin.';
    } elseif (!in_array($saat, saat_dilimleri(), true)) {
        $hatalar['saat'] = 'Seçilen saat dilimi geçersiz.';
    } elseif ($tarih === date('Y-m-d') && strtotime($tarih . ' ' . $saat) <= time()) {
        $hatalar['saat'] = 'Bugün için geçmiş bir saat seçilemez. Lütfen ileri bir saat seçin.';
    }

    if (mb_strlen($not, 'UTF-8') > 500) {
        $hatalar['not'] = 'Hasta notu en fazla 500 karakter olabilir.';
    }

    if (!isset($hatalar['tarih']) && !isset($hatalar['saat']) && $tarih !== '' && $saat !== '') {
        foreach ($randevular as $randevu) {
            if (($randevu['durum'] ?? '') === 'iptal') {
                continue;
            }

            if ((string) ($randevu['tarih'] ?? '') === $tarih && (string) ($randevu['saat'] ?? '') === $saat) {
                $hatalar['saat'] = 'Bu tarih ve saatte zaten bir randevu var. Lütfen başka bir saat seçin.';
                break;
            }
        }
    }

    return [
        'veri' => [
            'ad_soyad' => $adSoyad,
            'telefon'  => $telefon,
            'eposta'   => $eposta,
            'hizmet'   => $hizmet,
            'tarih'    => $tarih,
            'saat'     => $saat,
            'personel' => $personel,
            'not'      => $not,
            'sms'      => $sms,
        ],
        'hatalar' => $hatalar,
    ];
}

/* -------------------------------------------------------------------------
 * CSRF, flash ve yönlendirme
 * ---------------------------------------------------------------------- */

function csrf_token(): string
{
    oturum_hazirla();

    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']) || $_SESSION['csrf'] === '') {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_dogrula(mixed $token): bool
{
    oturum_hazirla();

    if (!is_string($token) || $token === '' || empty($_SESSION['csrf'])) {
        return false;
    }

    return hash_equals((string) $_SESSION['csrf'], $token);
}

function flash_ekle(string $tip, string $mesaj): void
{
    oturum_hazirla();

    if (!in_array($tip, ['basari', 'hata', 'bilgi'], true)) {
        $tip = 'bilgi';
    }

    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }

    $_SESSION['flash'][] = ['tip' => $tip, 'mesaj' => $mesaj];
}

/**
 * @return array<int, array{tip: string, mesaj: string}>
 */
function flash_mesajlari(): array
{
    oturum_hazirla();

    $mesajlar = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return is_array($mesajlar) ? $mesajlar : [];
}

function yonlendir(string $url): never
{
    if (!headers_sent()) {
        header('Location: ' . $url, true, 302);
    }

    exit;
}

function guvenli_donus(mixed $deger): string
{
    $deger = trim((string) $deger);

    if ($deger === '' || preg_match('/[\r\n]/', $deger)) {
        return 'index.php';
    }

    if (!preg_match('/^(index|ekle)\.php(\?[^\s]*)?(#[A-Za-z0-9_-]+)?$/', $deger)) {
        return 'index.php';
    }

    if (str_contains($deger, '://') || str_contains($deger, '//')) {
        return 'index.php';
    }

    return $deger;
}
