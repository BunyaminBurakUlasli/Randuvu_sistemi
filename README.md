# Yonca Hastanesi — Hastane Randevu Yönetim Sistemi

**Ders:** İnternet Programcılığı I (PHP)
**Proje konusu:** Hastane poliklinikleri için randevu kayıt ve takip sistemi
**Çalışma biçimi:** Bireysel
**Teknoloji:** PHP 8 + HTML5 + CSS3 + JavaScript (isteğe bağlı iyileştirmeler)
**Veri saklama:** PHP dizileri + `$_SESSION` 



## 1. Projenin Adı ve Amacı

**Yonca Hastanesi**, bir hastanenin poliklinik randevularını tek bir panelden
oluşturmasını, listelemesini, filtrelemesini ve yönetmesini sağlayan PHP tabanlı bir
kayıt ve takip sistemidir.

Projenin amacı yalnızca görsel bir sayfa hazırlamak değil; PHP ile veri okuyan, veri
doğrulayan, kullanıcı işlemlerine tepki veren ve sayfa yenilendiğinde verisini koruyan
çalışır bir uygulama ortaya koymaktır.

Kullanıcı;

- günün muayene programını ve genel istatistikleri tek ekranda görür,
- arama, poliklinik, durum ve tarih filtrelerini birlikte kullanabilir,
- yeni randevu oluşturabilir, hatalı girişlerde açıklayıcı mesaj alır,
- randevunun durumunu güncelleyebilir, randevuyu silebilir,
- tüm verileri örnek veriye geri döndürebilir.

Uygulama on dört poliklinik (Dahiliye, Kardiyoloji, Ortopedi, Göz, Diş, Dermatoloji,
Çocuk Sağlığı, KBB, Nöroloji, Üroloji, Kadın Doğum, Fizik Tedavi, Göğüs, Psikiyatri)
ve her poliklinikte **Op. Dr., Doç. Dr. ve Prof. Dr.** unvanlarıyla üçer hekim olmak
üzere toplam **42 doktor** seçeneği ile çalışır. Doktor listesi seçilen polikliniğe göre
gruplanır. Randevu saatleri **08:30 – 17:30** arasında 30 dakikalık dilimlerden oluşur.

---

## 2. Projede Bulunan Özellikler

### 2.1 Zorunlu özellikler

| Gereksinim | Uygulamadaki karşılığı |
|---|---|
| Ana sayfa, uygulama adı ve açıklaması | `index.php` — hero bölümü, tanıtım metni, poliklinikler ve “Neden Yonca Hastanesi?” bölümleri |
| Kayıtların PHP ile listelenmesi | `foreach` döngüsüyle tablo satırları ve mobil kart görünümü |
| Her kayıtta en az 4 bilgi alanı | Hasta adı, telefon/e-posta, poliklinik, doktor, tarih-saat, durum, hasta notu |
| Yeni kayıt ekleme | `ekle.php` — 4 adımlı randevu formu |
| POST ile veri gönderme | Ekleme, silme, durum güncelleme ve sıfırlama işlemlerinin tamamı `POST` |
| PHP tarafında veri doğrulama | `randevu_dogrula()` — zorunlu alan, biçim, tarih/saat ve çakışma kontrolleri |
| Kayıt silme | `sil.php` — yalnızca `POST` + CSRF doğrulaması ile |
| Kayıt filtreleme | Poliklinik, durum, tarih ve serbest metin araması (`GET`) |
| Özet bilgiler / sayaçlar | Toplam, bugün, bekleyen, onaylı, tahmini gelir ve doluluk göstergeleri |
| `htmlspecialchars()` ile güvenli çıktı | Tüm kullanıcı kaynaklı metinler `e()` yardımcı fonksiyonu üzerinden basılır |
| `$_SESSION` ile kalıcılık | Randevular, son ID, CSRF anahtarı ve bildirimler oturumda saklanır |
| En az 2 kullanıcı tanımlı fonksiyon | `randevu_dogrula()`, `filtrele_ve_sirala()`, `istatistikler()`, `takvim_verisi()`, `para_formatla()`, `turkce_tarih()` ve daha fazlası |
| Koşul yapıları | Doğrulama, filtreleme, sıralama, durum renkleri ve boş durum kontrolleri |
| HTML / CSS / PHP ayrımı | PHP mantığı kök dosyalarda, ortak HTML parçaları `includes/` altında, stiller `assets/css/style.css` içinde |

### 2.2 Ek özellikler

- **Yalnızca açık tema:** Koyu tema, tema düğmesi ve `localStorage` tema tercihi tamamen kaldırılmıştır. Arayüz her zaman açık teal–mavi paletle görüntülenir.
- **CSRF koruması:** Tüm yazma işlemleri (`ekle`, `sil`, `durum`, `sifirla`) tek kullanımlık oturum anahtarıyla doğrulanır.
- **Çakışma kontrolü:** Aynı tarih ve saatte ikinci bir randevu oluşturulması engellenir.
- **Geçmiş tarih/saat koruması:** Geçmiş tarih ve bugün için geçmiş saat seçilemez.
- **Durum yönetimi:** Bekliyor, Onaylandı, Tamamlandı ve İptal durumları arasında geçiş.
- **Gelişmiş listeleme:** Arama, poliklinik, durum, tarih filtreleri; akıllı, tarihe göre ve isme göre sıralama.
- **Filtre çipleri:** Aktif filtreler tek tek kaldırılabilir.
- **Mini takvim:** Aylık randevu yoğunluğu ve güne tıklayarak filtreleme.
- **Gösterge paneli:** Toplam kayıt, bugünkü randevu, bekleyen iş, tahmini gelir ve doluluk oranı.
- **Donut grafik:** Durum dağılımı.
- **Detay ve silme modalları:** JavaScript varsa şık onay penceresi; JavaScript yoksa normal `POST` formu çalışmaya devam eder.
- **Poliklinik bazlı doktor kadrosu:** Her poliklinikte Op. Dr., Doç. Dr. ve Prof. Dr. unvanlı üç hekim bulunur; doktor seçimi seçilen polikliniğe göre gruplanır ve uyuşmayan seçim otomatik olarak “Fark etmez”e döner.
- **Doktor rütbesine göre ücretlendirme:** Poliklinik taban ücretine doktor unvanı eklenir; Op. Dr. taban ücret, Doç. Dr. +%25, Prof. Dr. +%50. “Fark etmez” seçiminde taban ücret uygulanır.
- **Canlı randevu özeti:** Form doldurulurken poliklinik, tarih, saat, doktor, süre ve muayene ücreti anlık güncellenir.
- **Duyarlı tasarım:** Mobil menü, mobil kart tablosu ve kırılma noktaları.
- **Erişilebilirlik:** Klavye odağı, `aria-*` nitelikleri, “içeriğe geç” bağlantısı, renk kontrastı ve `prefers-reduced-motion` desteği.
- **Yazdırma desteği:** Menü, filtre ve işlem sütunları yazdırırken gizlenir.
- **Türkçe biçimlendirme:** Tarih, saat, telefon ve para birimi Türkçe biçimde gösterilir.

### 2.3 Tasarım paleti

Arayüz yalnızca açık temada çalışır ve şu renk ailesini kullanır:

| Rol | Renk |
|---|---|
| Ana marka (teal) | `#0f766e` |
| İkincil (cyan) | `#0891b2` |
| Vurgu (blue) | `#0284c7` |
| Yumuşak mint arka plan | `#f4faf9` / `#e8f5f2` |
| Metin (navy-teal) | `#0c2422` |
| Uyarı | `#d97706` |
| Hata / tehlike | `#dc2626` |

---

## 3. Klasör Yapısı

```text
Randuvu_sistemi/
│
├── index.php                 → Ana sayfa, filtreleme, listeleme ve istatistikler
├── ekle.php                  → Yeni randevu formu, doğrulama ve kayıt işlemi
├── sil.php                   → POST + CSRF korumalı kayıt silme
├── durum.php                 → POST + CSRF korumalı durum güncelleme
├── sifirla.php               → Örnek verileri geri yükleme
├── functions.php             → Veri, doğrulama, filtreleme ve biçimlendirme fonksiyonları
│
├── includes/
│   ├── header.php            → Ortak <head>, üst menü ve bildirimler
│   ├── footer.php            → CTA, alt bilgi, modallar ve script bağlantıları
│   └── icons.php             → Satır içi SVG tıbbi ikon kitaplığı
│
├── assets/
│   ├── css/
│   │   └── style.css         → Tüm stiller, açık tema değişkenleri ve duyarlı tasarım
│   ├── js/
│   │   └── app.js            → Menü, modal, canlı özet ve form etkileşimleri
│   └── img/
│       └── favicon.svg       → Uygulama simgesi (teal–cyan tıbbi artı)
│
├── ekran-goruntuleri/
│   ├── 01-ana-sayfa.png
│   ├── 02-yeni-randevu.png
│   └── 03-filtreleme.png
│
└── README.md
```

> `includes/` ve `assets/` klasörleri doğrudan tarayıcıdan açılmak için değil, PHP
> sayfaları tarafından dahil edilmek için kullanılır. `theme-init.js` dosyası koyu tema
> kaldırıldığı için silinmiştir.

---

## 4. XAMPP Üzerinde Çalıştırma

### Gereksinimler

- XAMPP (Apache + PHP 8.x). Proje **PHP 8.2.12** ile test edilmiştir.
- `mbstring` eklentisi (XAMPP'ta varsayılan olarak açıktır).
- Modern bir tarayıcı (Chrome, Edge, Firefox).

### Adım adım kurulum

1. XAMPP Control Panel'i açın ve **Apache** servisini başlatın.
2. Proje klasörünü `C:\xampp\htdocs\` altına kopyalayın. Örneğin:
   ```text
   C:\xampp\htdocs\Randuvu_sistemi\
   ```
3. Tarayıcıda şu adresi açın:
   ```text
   http://localhost/Randuvu_sistemi/index.php
   ```
4. İlk açılışta 10 adet örnek hastane randevusu otomatik olarak yüklenir.

### Alternatif: PHP yerleşik sunucusu

Apache kullanmak istemiyorsanız proje klasöründe şu komutu çalıştırabilirsiniz:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8080 -t "C:\xampp\htdocs\Randuvu_sistemi"
```

Ardından `http://127.0.0.1:8080` adresini açın.

> **Not:** Projeyi `file://` yoluyla doğrudan açmayın. PHP dosyaları yalnızca bir web
> sunucusu üzerinden çalışır.

### Verileri sıfırlama

Alt bilgideki **“Demo verilerini sıfırla”** düğmesi, oturumdaki tüm değişiklikleri
silip 10 örnek randevuyu yeniden yükler. Veriler tarayıcı oturumuna bağlı olduğu için
tarayıcı kapatıldığında oturum sona ererse örnek veriler yeniden oluşturulur.

---

## 5. Kullanım Rehberi

### Ana sayfa

- Üstteki sayaç kartları toplam kaydı, bugünkü randevuları, bekleyen işleri ve tahmini geliri gösterir.
- **Ara** alanı hasta adı, telefon, e-posta ve poliklinik adında arama yapar.
- **Poliklinik**, **Durum** ve **Tarih** alanları birlikte filtrelenebilir.
- Sıralama menüsüyle akıllı sıralama, tarih (artan/azalan), isim veya en yeni seçilebilir.
- Durum sekmeleri tek tıkla ilgili durumu filtreler.
- Takvimdeki bir güne tıklamak o güne ait randevuları listeler.
- Satırdaki göz simgesi randevu detaylarını, çöp kutusu simgesi silme onayını açar.
- Randevu durumu satırdaki menüden değiştirilebilir.

### Yeni randevu

1. **Poliklinik** seçin (14 poliklinik, süre ve muayene ücreti bilgisiyle).
2. **Tarih** ve **saat** belirleyin. Geçmiş saatler otomatik olarak devre dışı kalır.
3. **Hasta ve iletişim bilgileri**ni girin.
4. **Doktor** seçin (liste seçilen polikliniğe göre filtrelenir) ve varsa **hasta notu** ekleyin.
5. Sağdaki canlı özeti kontrol edip **“Randevuyu oluştur”** düğmesine basın.

### Ücretlendirme

- **Op. Dr.:** Poliklinik taban ücreti.
- **Doç. Dr.:** Taban ücret + %25.
- **Prof. Dr.:** Taban ücret + %50.
- **Fark etmez:** Taban ücret.
- Seçilen doktora göre tutar, yeni randevu formundaki canlı özette ve randevu detayında anlık güncellenir. Tahmini gelir göstergesi de bu tutarı kullanır.

### Doğrulama kuralları

- Ad soyad zorunlu, 3–80 karakter ve yalnızca harf/boşluk/`.`/`'`/`-` içerebilir.
- Telefon zorunlu ve Türkiye formatında olmalıdır (`05XX XXX XX XX`).
- E-posta isteğe bağlıdır; girilirse geçerli olmalıdır.
- Poliklinik, tarih ve saat zorunludur.
- Geçmiş tarih, bugün için geçmiş saat ve bir yıldan uzak tarih kabul edilmez.
- Aynı tarih ve saatte ikinci randevu oluşturulamaz.
- Hasta notu en fazla 500 karakter olabilir.

---

## 6. Test Senaryoları

Aşağıdaki senaryolar proje tesliminden önce çalıştırılmış ve başarıyla geçmiştir.

| # | Senaryo | Beklenen sonuç | Durum |
|---|---|---|---|
| 1 | Formu tamamen boş gönderme | Zorunlu alanlar için açıklayıcı hata mesajları | ✅ Geçti |
| 2 | Geçersiz e-posta ve telefon girme | Alan bazlı doğrulama mesajları | ✅ Geçti |
| 3 | Geçerli bilgilerle randevu ekleme | Kayıt listeye eklenir, başarı bildirimi görünür | ✅ Geçti |
| 4 | Aynı tarih/saat ile ikinci kayıt | “Bu tarih ve saatte zaten bir randevu var” uyarısı | ✅ Geçti |
| 5 | Bugün için geçmiş saat seçme | Saat alanında hata mesajı | ✅ Geçti |
| 6 | Kayıt durumunu “Onaylandı” yapma | Durum rozeti ve sayaçlar güncellenir | ✅ Geçti |
| 7 | `q=Elif` araması | Yalnızca eşleşen kayıt listelenir | ✅ Geçti |
| 8 | `hizmet=dermatoloji&durum=onaylandi` filtresi | İki koşula uyan kayıt listelenir, filtre çipleri görünür | ✅ Geçti |
| 9 | Takvimde ay değiştirme | Seçili ay URL'de korunur | ✅ Geçti |
| 10 | Kaydı POST ile silme | Kayıt listeden kalkar, başarı bildirimi görünür | ✅ Geçti |
| 11 | Geçersiz CSRF anahtarıyla silme | İşlem reddedilir, hata bildirimi görünür | ✅ Geçti |
| 12 | `sil.php` adresine GET ile erişme | İşlem reddedilir, kayıt silinmez | ✅ Geçti |
| 13 | Örnek verileri geri yükleme | 10 örnek randevu yeniden yüklenir | ✅ Geçti |
| 14 | Mobil görünüm (≈460 px) | Yatay kaydırma yok, menü ve kartlar düzgün | ✅ Geçti |
| 15 | Açık tema tutarlılığı | Tüm sayfalarda teal–mavi palet; koyu tema/tema düğmesi yok | ✅ Geçti |
| 16 | JavaScript kapalıyken silme | Normal `POST` formu ile silme çalışır | ✅ Geçti |

**Ek kontroller**

- Tüm PHP dosyaları `php -l` ile sözdizimi kontrolünden geçirildi.
- Sayfalar HTTP 200 döndürüyor; PHP uyarı/hata çıktısı yok.
- Tarayıcı konsolunda hata bulunmuyor.
- Lighthouse (Masaüstü): **Erişilebilirlik 1.00**, **En İyi Uygulamalar 1.00**, **SEO 1.00** (açık tema).
- `theme-init.js` dosyasına ve `data-theme` niteliğine hiçbir dosyada rastlanmıyor.

---

## 7. Güvenlik Notları

- Kullanıcıdan gelen tüm metinler ekrana basılırken `htmlspecialchars()` kullanan `e()` fonksiyonundan geçirilir.
- Yazma işlemleri yalnızca `POST` kabul eder; bağlantı ile silme engellenir.
- Her yazma işleminde `csrf_dogrula()` ile oturum anahtarı kontrol edilir.
- Oturum çerezi `HttpOnly` ve `SameSite=Lax` olarak ayarlanır; HTTPS varsa `Secure` eklenir.
- Yönlendirme adresleri `guvenli_donus()` ile yalnızca `index.php` ve `ekle.php` ile sınırlandırılır.
- Dosya sistemi veya veritabanı erişimi olmadığı için SQL enjeksiyonu riski bu sürümde yoktur; MySQL'e geçişte `PDO` ve hazır ifadeler (prepared statements) kullanılmalıdır.

---

## 8. Bilinen Eksikler ve Sonraki Adımlar

Bu teslim, projenin **ilk sürümüdür (MVP)**. Bilinen eksikler:

- Veriler `$_SESSION` içinde tutulur; tarayıcı oturumu sona erdiğinde kalıcı olarak saklanmaz.
- Kullanıcı girişi ve yetkilendirme yoktur.
- Kayıt güncelleme ekranı henüz yoktur (durum güncelleme vardır).
- Çakışma kontrolü tarih + saat bazındadır; doktor bazlı çakışma kontrolü eklenebilir.
- E-posta/SMS gönderimi simülasyondur; gerçek entegrasyon yoktur.
- Ekran görüntüleri `ekran-goruntuleri/` klasöründedir; teslimden önce güncel hâlleriyle değiştirilebilir.

Planlanan geliştirmeler:

1. MySQL + PDO ile kalıcı veri saklama.
2. Kayıt düzenleme (update) ekranı.
3. Kullanıcı adı/parola ile giriş ve rol bazlı yetkilendirme.
4. Doktor bazlı takvim ve çakışma kontrolü.
5. E-posta/SMS hatırlatmaları.
6. Dışa aktarma (CSV/PDF) ve gelişmiş raporlama.

---

## 9. Yapay Zekâ Kullanım Açıklaması

> Bu bölüm, ödev duyurusunun **“Kural 4 — Yapay zekâ kullanımını belirtmelisiniz”**
> maddesi gereğince hazırlanmıştır.

### 9.1 Kullanılan yapay zekâ araçları

- **OpenCode masaüstü uygulamasındaki yapay zekâ kodlama ajanı** (PHP, HTML, CSS ve JavaScript kodunun üretilmesi, düzenlenmesi ve test edilmesi).
- **Gemini** (proje konusu ve özellik listesi üzerine fikir alma, hata mesajları ve metin taslakları).

### 9.2 Kullanım amaçları

- Proje konusunun ve özellik kapsamının netleştirilmesi.
- PHP sayfalarının, ortak `includes/` dosyalarının ve yardımcı fonksiyonların yazılması.
- Form doğrulama kurallarının ve hata mesajlarının oluşturulması.
- Arayüz tasarımı, açık tema CSS değişkenleri ve duyarlı tasarım.
- JavaScript ile modal ve canlı randevu özeti gibi etkileşimlerin eklenmesi.
- Hataların ayıklanması, erişilebilirlik ve renk kontrastı düzeltmeleri.
- Test senaryolarının çalıştırılması ve README taslağının hazırlanması.

### 9.3 Kullanılan örnek istem (prompt)

```text
İnternet Programcılığı I dersi için PHP tabanlı bir randevu kayıt ve takip sistemi
hazırlıyorum. Konu: hastane poliklinik randevuları.

Şu zorunlu özellikleri karşılamalı:
- PHP + HTML + CSS, XAMPP ile çalışacak, MySQL kullanmayacak.
- Kayıtlar PHP dizilerinde ve $_SESSION içinde tutulacak.
- Ana sayfada randevular foreach ile listelenecek; her kayıtta en az dört alan olacak.
- Yeni kayıt formu POST ile çalışacak; boş ve hatalı alanlar PHP tarafında
  doğrulanıp açıklayıcı hata mesajı gösterilecek.
- Kayıt silme POST ile yapılacak.
- Poliklinik ve duruma göre filtreleme, toplam kayıt ve durum sayaçları olacak.
- Tüm kullanıcı metinleri htmlspecialchars() ile basılacak.
- En az iki anlamlı kullanıcı tanımlı fonksiyon yazılacak.

Tasarım açık temalı, teal–mavi sağlık paletli ve mobil uyumlu olsun; koyu tema
istemiyorum. HTML, CSS ve PHP ayrı dosyalarda dursun. Önce dosya yapısını ve
fonksiyonları planla, sonra kodu üret.
```

### 9.4 Öğrencinin kendi yaptığı geliştirmeler

- Proje konusunu (hastane randevu sistemi) ve poliklinik/doktor/durum listesini belirledim.
- Hangi alanların zorunlu olacağına ve iş kurallarına karar verdim.
- Uygulamayı XAMPP üzerinde çalıştırıp formu, filtreleri, silme ve durum güncelleme akışlarını test ettim.
- Hata mesajlarının ve Türkçe metinlerin anlaşılır olması için düzeltmeler istedim.
- Renk paletini, sayfa düzenini ve mobil görünümü kontrol edip geri bildirim verdim.
- Koyu temanın tamamen kaldırılmasını ve açık teal–mavi paletin uygulanmasını istedim.
- Ortaya çıkan kodları satır satır inceleyerek fonksiyonların ve oturum yapısının nasıl çalıştığını öğrendim.
- README ve ekran görüntülerini teslim formatına göre düzenledim.


## 10. Ekran Görüntüleri

Teslim için en az üç ekran görüntüsü `ekran-goruntuleri/` klasörüne eklenmiştir:

1. `01-ana-sayfa.png` — Ana sayfa, istatistikler ve randevu listesi.
2. `02-yeni-randevu.png` — Yeni randevu formu ve canlı özet.
3. `03-filtreleme.png` — “Bekliyor” durumuna göre filtrelenmiş liste.

---

## 11. Teslim Kontrol Listesi

- [x] Kaynak dosyalar (PHP, HTML, CSS, JS) hazır
- [x] `README.md` hazır
- [x] En az üç ekran görüntüsü hazır
- [x] Ana sayfa PHP sunucusunda açılıyor
- [x] Kayıtlar doğru şekilde listeleniyor
- [x] Yeni kayıt eklenebiliyor (POST)
- [x] Boş ve hatalı girişler kontrol ediliyor
- [x] Kayıt silinebiliyor (POST)
- [x] Kayıtlar poliklinik, durum, tarih ve metne göre filtrelenebiliyor
- [x] Toplam kayıt ve durum sayaçları çalışıyor
- [x] Kullanıcı girdileri `htmlspecialchars()` ile güvenli gösteriliyor
- [x] `$_SESSION` ile sayfa yenilendiğinde veriler korunuyor
- [x] En az iki anlamlı kullanıcı tanımlı fonksiyon var
- [x] En az bir `foreach` döngüsü ve koşul yapıları var
- [x] Koyu tema ve tema düğmesi tamamen kaldırıldı
- [x] Yapay zekâ kullanımı, amaçları, örnek istem ve öğrenci katkısı belirtildi
- [x] ZIP dosyası `OgrenciNo_Ad_Soyad_PHP_Proje.zip` adıyla oluşturuldu
- [x] Proje Microsoft Teams üzerindeki ödev alanına yüklendi
- [ ] Kod açıklaması ve canlı gösterim için hazırlık yapıldı

---

## 12. Kısa Teknik Özet

| Konu | Açıklama |
|---|---|
| Dil / sürüm | PHP 8.2 |
| Sunucu | Apache (XAMPP) veya PHP yerleşik sunucusu |
| Veri kaynağı | `functions.php` içindeki diziler + `$_SESSION` |
| Form yöntemleri | Ekleme/silme/güncelleme: `POST`, filtreleme: `GET` |
| Güvenlik | `htmlspecialchars()`, CSRF, POST zorunluluğu, güvenli yönlendirme |
| Fonksiyonlar | `randevu_dogrula`, `filtrele_ve_sirala`, `istatistikler`, `takvim_verisi`, `para_formatla`, `turkce_tarih`, `csrf_token`, `csrf_dogrula`, `guvenli_donus`, `poliklinik_doktorlari`, `doktor_zam_orani`, `doktor_ucreti` |
| Tema | Yalnızca açık tema; teal `#0f766e`, cyan `#0891b2`, blue `#0284c7` |
| Dış bağımlılık | Google Fonts (Manrope + Playfair Display); başka kütüphane yok |
| Tarayıcı desteği | Güncel Chrome, Edge, Firefox, Safari |

---

**Hazırlayan:** Bünyamin Burak Ulaşlı 
**Öğrenci No:** 254309049
**Teslim tarihi:** 8 Kasım 2026
