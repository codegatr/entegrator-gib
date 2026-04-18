# Yol Haritası — CODEGA GİB Onaylı Entegratör

Bu dosya, `entegrator-gib` kütüphanesinin teknik yol haritasını VE CODEGA'nın Özel Entegratör olma sürecinin tüm adımlarını gösterir.

Son güncelleme: 2026-04-18

---

## Genel Süreç (12-18 ay hedef)

```
Ay 0-1     PREREQUISITES  ◀── ŞU ANDAYIZ
Ay 1-3     Core Library (yazılım)
Ay 3-5     Portal & API
Ay 5-7     Operasyon
Ay 6-10    ISO 27001 + DRC (paralel)
Ay 8-12    Prod altyapı
Ay 10-15   GİB başvuru + TÜBİTAK denetim
Ay 15-18   Canlı geçiş
```

---

## Faz 0: Prerequisites (Ay 0-1)

**CODEGA tarafı (yazılım dışı):**

- [ ] A.Ş. yapısı doğrulanması (mali müşavir ile)
- [ ] Minimum sermaye tutarı (GİB'in güncel tavan-taban bilgisi)
- [ ] **TÜBİTAK Kamu SM** test mali mühür başvurusu (~2 hafta süre)
- [ ] **GİB Bilgi Transfer Sistemi (BİS)** kayıt
- [ ] **GİB e-Fatura test ortamı** erişim talebi (resmi yazı)
- [ ] **ISO 27001** danışman firmasıyla anlaşma (3 teklif karşılaştır)
- [ ] **Bilişim hukuku avukatı** (Konya Barosu)
- [ ] **DRC (ikinci lokasyon)** planlaması — İstanbul/Ankara DC seçimi
- [ ] **KVKK VERBİS** kaydı (yoksa)
- [ ] Sermaye + bütçe planı (~1-2M ₺)

---

## Faz 1: Core Library (Ay 1-3) ◀── ŞU ANDA

**Yazılım — bu repo:**

### v0.1 — Temel XML ✅ TAMAMLANDI (2026-04-18)

- [x] `Invoice\UblBuilder` + Models
- [x] Util (Uuid, AmountFormatter)
- [x] Exception tree
- [x] 3 satır çalışan örnek

### v0.2 — İmzalama

- [ ] `Sign\MaliMuhurCert` — P12/PKCS12 yükleyici
  - Şifre çözme
  - Sertifika zinciri parse
  - Anahtar / sertifika ayrımı
- [ ] `Sign\XAdESSigner` — XAdES-BES imzalama
  - Canonicalization (C14N)
  - SignedInfo oluşturma
  - RSA-SHA256 imza
  - xades:QualifyingProperties üretimi
  - İmzalanmış XML'i UBLExtensions altına enjeksiyon
- [ ] `examples/02_signed_invoice.php` — imzalı XML üretim örneği

### v0.3 — GİB Test Ortamı POC 🎯 HEDEFİMİZ

- [ ] `Service\GibClient` — SOAP + REST wrapper
  - Authenticate
  - sendInvoice
  - getInvoiceStatus
  - getInvoiceDetail
  - cancelInvoice
- [ ] `Service\MukellefSorgu` — VKN/TCKN → e-fatura mükellefi mi?
- [ ] `examples/03_send_to_gib_test.php` — **gerçek test ortamı gönderimi**

### v0.4 — Doğrulama + Alternatif Senaryolar

- [ ] `Invoice\UblValidator` — XSD şema doğrulama
- [ ] `examples/04_earsiv_invoice.php` — son kullanıcı faturası
- [ ] `examples/05_ihracat_invoice.php` — dış satış
- [ ] `examples/06_iade_invoice.php` — iade
- [ ] `examples/07_tevkifat_invoice.php` — tevkifatlı

### v0.5 — Arşiv

- [ ] `Archive\StorageInterface`
- [ ] `Archive\DiskStorage` — lokal disk
- [ ] `Archive\S3Storage` — MinIO/AWS S3 uyumlu
- [ ] 10 yıllık saklama index sistemi

---

## Faz 2: Portal & API (Ay 3-5)

**`codegatr/entegrator-portal` repo (ayrı):**

- [ ] Müşteri portal UI (login, fatura listesi, yeni fatura, PDF, arama)
- [ ] Public REST API v1 (mükelleflerin fatura göndermesi için)
- [ ] Webhook sistemi (durum bildirim)
- [ ] Admin paneli (iç operasyon)
- [ ] KVKK uyumlu audit log altyapısı

---

## Faz 3: Operasyon (Ay 5-7)

- [ ] Abonelik / kontör yönetimi
- [ ] Tahsilat + iyzico/Stripe entegrasyonu
- [ ] Destek talep sistemi
- [ ] SMS / e-posta bildirim sistemi
- [ ] Raporlama (yasal + iç)

---

## Faz 4: ISO 27001 + DRC (Ay 6-10, paralel)

**Yasal/Operasyonel — CODEGA tarafı:**

- [ ] ISO 27001 risk analizi
- [ ] Dokümantasyon (politikalar, prosedürler, talimatlar)
- [ ] Kontrol uygulamaları (114 kontrol)
- [ ] İç denetim
- [ ] **Bağımsız dış denetim**
- [ ] **Sertifika alımı** (Stage 1 + Stage 2)
- [ ] DRC kurulum + senkron test
- [ ] Pentest (bağımsız firma tarafından)
- [ ] Vulnerability scan
- [ ] KVKK uyumluluk denetimi

---

## Faz 5: Prod Altyapı (Ay 8-12)

- [ ] HSM (Hardware Security Module) entegrasyonu — mali mühür güvenliği
- [ ] WAF + DDoS koruma (Cloudflare Enterprise veya eşdeğer)
- [ ] Monitoring (Prometheus + Grafana + alerting)
- [ ] Uptime %99.5+ SLA altyapısı
- [ ] Yük testleri (hedef: 10K fatura/saat)
- [ ] Disaster recovery drill
- [ ] Geliştirme + staging + prod ortamları ayrımı

---

## Faz 6: GİB Başvuru + Denetim (Ay 10-15) 🎯 KRİTİK

- [ ] GİB'e formal başvuru dosyası
- [ ] Test ortamında resmi test senaryoları (10-15 farklı senaryo)
- [ ] Hepsi başarıyla geçtiğinde GİB dosyayı BİLGEM'e yönlendirir
- [ ] **TÜBİTAK BİLGEM Bilgi Sistemleri Denetimi**
- [ ] Eksik bulgular için düzeltme turları (1-3 iter)
- [ ] Final onay → **ebelge.gib.gov.tr listesine eklenme** 🎉

---

## Faz 7: Canlı Yayın (Ay 15-18)

- [ ] Pilot müşteriler (5-10 firma)
- [ ] 7/24 destek hattı
- [ ] İlk 3 aylık gözlem + iyileştirme
- [ ] Pazarlama — reklam, duyuru, sektör etkinlikleri
- [ ] Ölçeklendirme planı

---

## Başarı Kriterleri

**v0.1** (bu repo):
- ✅ Geçerli UBL-TR 2.1 XML üret

**v0.3** (3. sürüm):
- 🎯 GİB test ortamına 1 fatura başarıyla gönder/al

**Mayıs 2027** (1 yıl sonra hedef):
- GİB resmi başvuru yapılmış
- BİLGEM denetiminde
- Sertifikasyon adım adım tamamlanıyor

**Mayıs 2028** (2 yıl sonra hedef):
- 🏆 ebelge.gib.gov.tr listesinde CODEGA
- 50+ aktif mükellef müşteri
- Aylık 100K+ fatura hacmi

---

## Risk Kayıt

| Risk | Olasılık | Etki | Azaltma |
|---|---|---|---|
| GİB test senaryolarında ret | Yüksek | Orta | Erken test + iterasyon |
| TÜBİTAK denetiminde eksik | Yüksek | Yüksek | Deneyimli danışman |
| ISO 27001 gecikme | Orta | Yüksek | 6 ay öncesinden başla |
| Sermaye yetmezse | Orta | Kritik | Etap etap harcama + destek programları |
| Büyük oyuncular fiyat kırar | Yüksek | Orta | Niş konumlanma |
| Teknik personel eksikliği | Orta | Yüksek | Erken işe alım |
