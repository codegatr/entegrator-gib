# UBL-TR Standart Notları

Bu dosya, CODEGA entegratör altyapısını geliştirirken başvurulan GİB UBL-TR standartlarının **pratik özetidir**. Resmi kılavuz her zaman öncelik taşır; bu dosyada bulunan bilgiler hızlı referans amaçlıdır.

## Sürüm

| Bileşen | Sürüm |
|---|---|
| UBL (OASIS) | 2.1 |
| UBL-TR Customization | 1.2 |
| CustomizationID elementi | `TR1.2` |
| UBLVersionID elementi | `2.1` |

## XML Namespace'ler

| Önek | URI | Kullanım |
|---|---|---|
| (ana) | `urn:oasis:names:specification:ubl:schema:xsd:Invoice-2` | Root element `<Invoice>` |
| `cac` | `urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2` | Aggregate (Party, Address...) |
| `cbc` | `urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2` | Basic (ID, Name, Amount...) |
| `ext` | `urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2` | UBLExtensions (imza) |
| `ds` | `http://www.w3.org/2000/09/xmldsig#` | XML-DSig (imza) |

## Element Sırası (kritik!)

UBL-TR şema kuralı gereği root `<Invoice>` altındaki elementlerin sırası **değişmez** olmalıdır. Aksi halde XSD validasyon hatası alınır.

```xml
<Invoice>
  <ext:UBLExtensions>         <!-- 1. İmza extension container -->
  <cbc:UBLVersionID>          <!-- 2. "2.1" -->
  <cbc:CustomizationID>       <!-- 3. "TR1.2" -->
  <cbc:ProfileID>             <!-- 4. TEMELFATURA / TICARIFATURA / ... -->
  <cbc:ID>                    <!-- 5. Fatura numarası -->
  <cbc:CopyIndicator>         <!-- 6. false (asıl), true (kopya) -->
  <cbc:UUID>                  <!-- 7. ETTN (UUID v4) -->
  <cbc:IssueDate>             <!-- 8. YYYY-MM-DD -->
  <cbc:IssueTime>             <!-- 9. HH:MM:SS (opsiyonel ama önerilen) -->
  <cbc:InvoiceTypeCode>       <!-- 10. SATIS / IADE / ... -->
  <cbc:Note>                  <!-- 11-N. 0..N adet açıklama (opsiyonel) -->
  <cbc:DocumentCurrencyCode>  <!-- Para birimi -->
  <cbc:LineCountNumeric>      <!-- Satır adedi -->
  <cac:OrderReference>        <!-- Opsiyonel: sipariş referansı -->
  <cac:DespatchDocumentReference>  <!-- Opsiyonel: irsaliye referansı -->
  <cac:AdditionalDocumentReference><!-- Opsiyonel: ek belge -->
  <cac:AccountingSupplierParty>   <!-- Satıcı -->
  <cac:AccountingCustomerParty>   <!-- Alıcı -->
  <cac:Delivery>              <!-- Opsiyonel: teslim bilgisi -->
  <cac:PaymentMeans>          <!-- Opsiyonel: ödeme yöntemi -->
  <cac:PaymentTerms>          <!-- Opsiyonel: ödeme koşulları -->
  <cac:AllowanceCharge>       <!-- Opsiyonel: fatura bazlı iskonto/ek -->
  <cac:TaxExchangeRate>       <!-- Opsiyonel: vergi kuru -->
  <cac:TaxTotal>              <!-- Toplam vergi + KDV oran breakdown -->
  <cac:LegalMonetaryTotal>    <!-- LineExt / TaxExcl / TaxIncl / Payable -->
  <cac:InvoiceLine>           <!-- 1..N adet fatura satırı -->
</Invoice>
```

## Profiller

| Profil | Ne zaman? | Kabul/red süreci? |
|---|---|---|
| **TEMELFATURA** | B2B, alıcı e-fatura mükellefi, standart satış | Yok (direkt geçer) |
| **TICARIFATURA** | B2B, alıcı kabul/red etme hakkına sahip | Var (8 gün süre) |
| **EARSIVFATURA** | Alıcı e-fatura mükellefi **değil** (son kullanıcı veya vergiden muaf) | N/A |
| **IHRACAT** | Yurt dışına satış, gümrük beyannameli | N/A |

Dikkat: EARSIVFATURA aslında UBL-TR 2.1'in alt profili; e-Fatura sisteminde yer almaz ama UBL-TR kullanır.

## Fatura Türleri (InvoiceTypeCode)

| Kod | Türkçe | Açıklama |
|---|---|---|
| SATIS | Satış faturası | Standart |
| IADE | İade faturası | Alıcıdan mal/hizmet geri gelmesi |
| TEVKIFAT | Tevkifatlı | KDV tevkifat kesintili |
| ISTISNA | İstisna | KDV istisnası (ör. KDVsiz ihracat) |
| OZELMATRAH | Özel matrah | Özel matrah uygulanmış kalemler |
| IHRACKAYITLI | İhraç kayıtlı | İhraç etmek üzere satın alınan mal |

## Mükellef Türleri (PartyIdentification/ID schemeID)

| schemeID | Anlamı | Uzunluk |
|---|---|---|
| `VKN` | Vergi Kimlik Numarası | 10 hane |
| `TCKN` | T.C. Kimlik Numarası | 11 hane |

**Not:** Eski UBL-TR 1.1 dökümanlarında `VKN_TCKN` tek kod vardı. 1.2'de ayrıldı. Kütüphanemiz uzunluğa bakarak otomatik tespit yapar.

## Tutar / Oran Formatları

GİB'in çok net kuralları var — kırma durumunda XSD ret verir:

| Alan | Ondalık | Örnek |
|---|---|---|
| Tutar (amount) — matrah, KDV, toplam | 2 basamak | `1250.00` |
| Birim fiyat (price) | 4 basamak | `1250.0000` |
| Miktar (quantity) | 8 basamak | `1.00000000` |
| Oran (percent) | 2 basamak | `20.00` |
| Kur (exchange rate) | 4 basamak | `32.5000` |

**Kritik kurallar:**
- Ondalık ayraç daima **nokta** (`.`), virgül YASAK
- Binlik ayraç YOK (`1250.00`, `1,250.00` değil)
- Negatif tutar için eksi işareti (`-150.00`, parantez YOK)
- `currencyID` attribute zorunlu her tutar elementinde

## KDV Kodlaması

`cac:TaxScheme/cbc:TaxTypeCode` için GİB standart kodları (kısa liste):

| Kod | Anlamı |
|---|---|
| `0015` | KDV (standart) |
| `9015` | KDV Tevkifat |
| `0011` | ÖTV |
| `0059` | KDV İstisna |

## ETTN (UUID v4)

Evrensel Tekil Tanımlayıcı Numarası. Her fatura için **benzersiz** üretilmeli.

```
550e8400-e29b-41d4-a716-446655440000
         ^                ^
         v4 göstergesi    RFC 4122 variant (8/9/a/b)
```

Aynı mükellef, aynı ETTN ile iki fatura gönderirse **GİB ret verir**. ETTN + VKN birleşimi sistemde unique key.

## Fatura Numarası Formatı

16 karakter: 3 büyük harf (seri) + 13 rakam (4 yıl + 9 sıra).

```
SF02026000000001
^^^             seri kodu (firma seçer)
   ^^^^         yıl (2026)
       ^^^^^^^^^  9 haneli sıra (örn. 000000001)
```

- Seri kodu her firma kendi belirler (ör. CODEGA için `COD`)
- Yıl her yılbaşında sıfırlanır (`000000001`'den başlar)
- Eksik sıfır dolgusu var (`00000001` → `000000001`)

## XAdES İmza (UBLExtensions içinde)

Mali mühür imzası `<ext:UBLExtensions>` → `<ext:UBLExtension>` → `<ext:ExtensionContent>` altına **XAdES-BES** formatında konur. Detaylar sonraki sürümde (`Sign\XAdESSigner`).

```xml
<ext:UBLExtensions>
  <ext:UBLExtension>
    <ext:ExtensionContent>
      <ds:Signature Id="Signature"
                    xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <ds:SignedInfo>...</ds:SignedInfo>
        <ds:SignatureValue>...</ds:SignatureValue>
        <ds:KeyInfo>...</ds:KeyInfo>
        <ds:Object>
          <xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#">
            ...
          </xades:QualifyingProperties>
        </ds:Object>
      </ds:Signature>
    </ext:ExtensionContent>
  </ext:UBLExtension>
</ext:UBLExtensions>
```

## Test Ortamı

GİB iki ayrı test ortamı sunar:

| Ortam | URL | Kullanım |
|---|---|---|
| **efaturatest.gib.gov.tr** | https://efaturatest.gib.gov.tr | Entegrasyon testi, akreditasyon öncesi |
| **rapor.efatura.gov.tr** | Canlı rapor ortamı | Sadece yetkili entegratörler |

Entegratör olmak için test ortamında **resmi test senaryoları** başarıyla geçmek gerekir (genelde 10-15 farklı senaryo — SATIS, IADE, TEVKIFAT, vb.).

## Ek Okuma

Resmi kılavuzlar:
1. [e-Fatura UBL-TR 1.2 Paket Kılavuzu](https://ebelge.gib.gov.tr/dosyalar/kilavuzlar)
2. [e-Arşiv UBL-TR Paket Kılavuzu](https://ebelge.gib.gov.tr/dosyalar/kilavuzlar)
3. [Özel Entegratörler BİS Denetim Kılavuzu](https://ebelge.gib.gov.tr/dosyalar/kilavuzlar/e-BELGE_OZEL_ENTEGRATORLERI_BILGI_SISTEMLERI_DENETIMI_KILAVUZU.pdf)
4. [UBL 2.1 OASIS Standart](http://docs.oasis-open.org/ubl/UBL-2.1.html)
5. [ETSI XAdES EN 319 132-1](https://www.etsi.org/deliver/etsi_en/319100_319199/31913201)
