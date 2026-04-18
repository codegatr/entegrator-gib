# entegrator-gib

**GİB Onaylı e-Fatura Özel Entegratör Altyapı Kütüphanesi** · PHP 8.1+

> ⚠️ **Erken aşama (alpha)**. Bu kütüphane CODEGA'nın GİB Onaylı Özel Entegratör olma sürecinde kullanılacak iç altyapısının temelidir. Production kullanıma **hazır değildir**. İlerleme için [CHANGELOG.md](CHANGELOG.md) bakınız.

Bu kütüphane; Türkiye'de Gelir İdaresi Başkanlığı (GİB) tarafından onaylı bir **e-Fatura Özel Entegratörü** olmak için gerekli teknik altyapının **PHP bileşenlerini** sağlar.

---

## Amaç

CODEGA'yı (veya CODEGA benzeri bir yazılım şirketini) 12-18 ay içinde **ebelge.gib.gov.tr listesinde yer alan bir özel entegratör** haline getirmek. Bu listeye girmenin gerekliliği sadece yazılım değil; ISO 27001 belgesi, mali mühür, TÜBİTAK BİLGEM denetimi ve minimum bir milyonluk sermayeyi kapsar. Bu repo **yazılım bileşenlerine** odaklanır.

## Kapsam

| Bileşen | Durum | Açıklama |
|---|---|---|
| **UBL-TR 2.1 XML Builder** | ✅ v0.1 | Fatura modelini UBL-TR XML'ine çevirir |
| **XAdES-BES Signer** | 🔜 sonraki sprint | Mali mühürle XML imzalama |
| **Mali Mühür (NES) yükleyici** | 🔜 | TÜBİTAK Kamu SM'den alınan sertifika entegrasyonu |
| **GİB SOAP Client** | 🔜 | Test ortamına bağlanma + gönderim |
| **UBL-TR Validator** | 🔜 | XSD şema doğrulama |
| **Mükellef Sorgu** | 🔜 | VKN/TCKN → e-fatura mükellefi mi? |
| **Archive** | 🔜 | S3-uyumlu 10 yıllık arşiv |

## Gereksinimler

- PHP 8.1 veya üzeri (8.3 önerilen)
- PHP eklentileri: `dom`, `libxml`, `openssl`, `curl`, `soap`, `mbstring`, `json`
- MySQL / MariaDB (kullanım senaryosu)

## Kurulum

```bash
composer require codegatr/entegrator-gib
```

Veya Composer yoksa, klasik PHP autoload ile:

```php
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'CodegaGib\\')) {
        $path = __DIR__ . '/vendor/entegrator-gib/src/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($path)) require $path;
    }
});
```

## Hızlı Başlangıç

```php
use CodegaGib\Invoice\UblBuilder;
use CodegaGib\Invoice\Models\{Party, Address, InvoiceLine};

$supplier = new Party(
    name:      'CODEGA Yazılım Hizmetleri',
    taxId:     '1234567890',
    address:   Address::tr('Konya', 'Selçuklu', 'Yazılım Cad.', '42'),
    taxOffice: 'Selçuk',
    email:     'info@codega.com.tr',
);

$customer = new Party(
    name:      'Örnek Müşteri A.Ş.',
    taxId:     '9876543210',
    address:   Address::tr('İstanbul', 'Kadıköy'),
    taxOffice: 'Kadıköy',
);

$xml = (new UblBuilder())
    ->setInvoiceNumber('COD2026000000001')
    ->setIssueDate(new DateTimeImmutable())
    ->setSupplier($supplier)
    ->setCustomer($customer)
    ->addLine(new InvoiceLine(
        id: 1,
        itemName: 'CodeGa ERP Aylık',
        quantity: 1,
        unitPrice: 1250.00,
        vatRate: 20.0,
    ))
    ->build();

echo $xml;  // UBL-TR 2.1 uyumlu XML
```

Tam çalışan örnek: [`examples/01_basic_invoice.php`](examples/01_basic_invoice.php)

```bash
php examples/01_basic_invoice.php
# → examples/output/01_basic_invoice.xml  (6.9 KB)
```

## Mimari

```
src/CodegaGib/
├── Exception/              CodegaGib-özel istisnalar
│   ├── CodegaGibException.php
│   └── InvalidInvoiceDataException.php
├── Util/                   Yardımcılar
│   ├── Uuid.php            RFC 4122 v4 UUID (ETTN için)
│   └── AmountFormatter.php GİB tutar/oran format kuralları
├── Invoice/                Fatura üretim katmanı
│   ├── Models/             Değer nesneleri (readonly)
│   │   ├── Address.php
│   │   ├── Party.php       VKN/TCKN auto-detect
│   │   └── InvoiceLine.php Matrah/KDV/toplam hesap
│   └── UblBuilder.php      Ana UBL-TR XML builder (fluent API)
├── Sign/                   🔜 İmzalama
├── Service/                🔜 GİB entegrasyonu
└── Archive/                🔜 Arşiv (10 yıl)
```

## Desteklenen Fatura Profilleri

| Profil | Sabit | Açıklama |
|---|---|---|
| TEMELFATURA | `UblBuilder::PROFILE_TEMEL` | Kabul/red süreci yok, direkt geçer |
| TICARIFATURA | `UblBuilder::PROFILE_TICARI` | Kabul/red süreci var (B2B) |
| EARSIVFATURA | `UblBuilder::PROFILE_EARSIV` | Son kullanıcı / e-fatura mükellefi olmayan |
| IHRACAT | `UblBuilder::PROFILE_IHRACAT` | Gümrük beyannameli dış satış |

## Desteklenen Fatura Türleri

| Tür | Sabit | Açıklama |
|---|---|---|
| SATIS | `UblBuilder::TYPE_SATIS` | Normal satış faturası |
| IADE | `UblBuilder::TYPE_IADE` | İade faturası |
| TEVKIFAT | `UblBuilder::TYPE_TEVKIFAT` | KDV tevkifatlı |
| ISTISNA | `UblBuilder::TYPE_ISTISNA` | KDV istisnası |
| OZELMATRAH | `UblBuilder::TYPE_OZELMATRAH` | Özel matrah |
| IHRACKAYITLI | `UblBuilder::TYPE_IHRACKAYITLI` | İhraç kayıtlı satış |

## Birim Kodları (UN/ECE Rec. 20)

`InvoiceLine` sabitleri:

- `UNIT_ADET` = `C62` — adet
- `UNIT_KG` = `KGM` — kilogram
- `UNIT_LITRE` = `LTR` — litre
- `UNIT_METRE` = `MTR` — metre
- `UNIT_M2` = `MTK` — metrekare
- `UNIT_SAAT` = `HUR` — saat
- `UNIT_GUN` = `DAY` — gün
- `UNIT_SET` = `SET`, `UNIT_KUTU` = `BX`, `UNIT_PAKET` = `PK`

## Yol Haritası

**v0.1.x — Core Library (mevcut)**
- [x] UBL-TR 2.1 XML Builder
- [x] Party/Address/InvoiceLine value objects
- [x] AmountFormatter + UUID util
- [x] Namespace-clean XML output

**v0.2.x — İmzalama**
- [ ] `Sign\XAdESSigner` — XAdES-BES
- [ ] `Sign\MaliMuhurCert` — PKCS12/P12 yükleyici
- [ ] İmzalı XML doğrulama

**v0.3.x — GİB Entegrasyon**
- [ ] `Service\GibClient` — SOAP + REST
- [ ] Test ortamı bağlantısı (efaturatest.gib.gov.tr)
- [ ] `Service\MukellefSorgu` — VKN/TCKN sorgulama
- [ ] İlk **gerçek test faturası** gönderim/alım

**v0.4.x — Doğrulama + Genişletme**
- [ ] `Invoice\UblValidator` — XSD şema
- [ ] e-Arşiv, İhracat, İade senaryoları
- [ ] PDF dönüştürme (xsl-fo)

**v0.5.x — Arşiv**
- [ ] `Archive\StorageInterface` — Disk/S3
- [ ] 10 yıl arşiv + hızlı arama
- [ ] Yasal erişim log'ları

## Standart Referansları

- [GİB e-Fatura Genel Tebliği (487 nolu)](https://www.mevzuat.gov.tr)
- [GİB e-Belge Özel Entegratörleri Bilgi Sistemleri Denetimi Kılavuzu](https://ebelge.gib.gov.tr)
- [UBL-TR 1.2 Paket Kılavuzu](https://ebelge.gib.gov.tr/dosyalar/kilavuzlar)
- [GİB Test Ortamı Bilgileri](https://efaturatest.gib.gov.tr)
- [UBL 2.1 OASIS Standard](http://docs.oasis-open.org/ubl/UBL-2.1.html)

## Katkı

Bu repo CODEGA'nın özel projesidir. Pull request kabul edilmez; ancak Issue açarak hata bildirimi ve öneri yapabilirsiniz.

## Lisans

MIT License — bkz. [LICENSE](LICENSE)

## İletişim

- Website: [codega.com.tr](https://codega.com.tr)
- ERP: [erp.codega.com.tr](https://erp.codega.com.tr)
- Entegratör projesi: [entegrator.codega.com.tr](https://entegrator.codega.com.tr)
