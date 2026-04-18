# Changelog

Tüm önemli değişiklikler bu dosyada kayıt altına alınır.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
Sürümleme: [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

## [0.1.0-alpha] — 2026-04-18

**İlk yayın — Core Library temeli**

### Eklendi

- `CodegaGib\Invoice\UblBuilder` — UBL-TR 2.1 uyumlu e-Fatura / e-Arşiv XML builder
  - Fluent API (method chaining)
  - 4 profil desteği: TEMELFATURA, TICARIFATURA, EARSIVFATURA, IHRACAT
  - 6 fatura türü: SATIS, IADE, TEVKIFAT, ISTISNA, OZELMATRAH, IHRACKAYITLI
  - Otomatik ETTN (UUID v4) üretimi
  - KDV oranına göre TaxSubtotal gruplama
  - Namespace-clean XML output (15 KB → 7 KB boyut iyileştirmesi)
- `CodegaGib\Invoice\Models\Party` — Readonly value object
  - VKN/TCKN otomatik tespit (10 / 11 hane)
  - Tüzel kişi için vergi dairesi zorunluluğu validasyonu
  - E-posta format doğrulama
- `CodegaGib\Invoice\Models\Address` — UBL PostalAddress için readonly VO
  - `Address::tr()` statik fabrika metodu (Türkiye kısayolu)
- `CodegaGib\Invoice\Models\InvoiceLine` — Fatura satırı VO
  - Matrah, KDV ve toplam otomatik hesaplanır
  - 10 birim kodu sabit (UN/ECE Rec. 20)
  - Satır bazlı iskonto desteği
- `CodegaGib\Util\Uuid` — RFC 4122 v4 UUID üreteci
- `CodegaGib\Util\AmountFormatter` — GİB tutar/oran format kuralları
  - 4 hassasiyet seviyesi: amount (2), price (4), quantity (8), percent (2)
  - Türkçe virgül → nokta dönüşümü
  - Toplam tutar tutarlılık kontrolü (±0.05 TL tolerans)
- `CodegaGib\Exception\CodegaGibException` — Temel istisna
- `CodegaGib\Exception\InvalidInvoiceDataException` — Veri validasyon istisnası
- `examples/01_basic_invoice.php` — 3 satırlık SATIS faturası örneği
- `composer.json` — PSR-4 autoload, PHP 8.1+ gereksinimi
- MIT lisansı
- Dökümantasyon: README, CHANGELOG

### Doğrulandı

- Well-formed XML üretimi (DOM validasyonu)
- Çıktı UBL-TR 2.1 element sırasına uygun
- Matrah + KDV = Toplam hesap doğruluğu (5.500 + 1.100 = 6.600)
- `<ext:UBLExtensions>` container hazır — XAdES imza sonraki sürümde eklenecek

### Henüz yok (sonraki sürümler)

- XML şema (XSD) doğrulama
- XAdES-BES imzalama
- Mali mühür sertifikası entegrasyonu
- GİB test ortamı bağlantısı
- Mükellef sorgulama
- Arşiv modülü

### Bilinen Kısıtlar

- PartyTaxScheme içinde sadece vergi dairesi adı yazılır (ileride PartyTaxScheme/TaxScheme/ID de eklenebilir)
- IHRACAT profili için cac:Delivery / cac:GoodsItem bölümleri henüz oluşturulmuyor
- TEVKIFAT için özel vergi kalemleri henüz yok
- Para birimi dönüşüm kuru (cbc:TaxExchangeRate) desteği yok
- Satır bazlı iskonto var; fatura bazlı iskonto (`cac:AllowanceCharge` root seviyesi) henüz yok
