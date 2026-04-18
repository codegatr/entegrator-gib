<?php

declare(strict_types=1);

/**
 * Örnek 01: Basit Satış Faturası
 * ─────────────────────────────────
 * CODEGA → bir müşteri firma için TEMELFATURA üretir.
 * 3 satır, TRY, KDV %20, iskonto yok.
 *
 * Çalıştır:  php examples/01_basic_invoice.php
 * Çıktı:     examples/output/01_basic_invoice.xml
 */

// Önce Composer autoload'u dene (composer install yapılmışsa)
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

// Composer yoksa / tam yüklenmediyse manuel PSR-4 bootstrap
if (!class_exists(\CodegaGib\Invoice\UblBuilder::class)) {
    spl_autoload_register(function (string $class): void {
        if (str_starts_with($class, 'CodegaGib\\')) {
            $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($path)) {
                require $path;
            }
        }
    });
}

use CodegaGib\Invoice\Models\Address;
use CodegaGib\Invoice\Models\InvoiceLine;
use CodegaGib\Invoice\Models\Party;
use CodegaGib\Invoice\UblBuilder;

// ── Satıcı: CODEGA ─────────────────────────────────────────────
$supplier = new Party(
    name:      'CODEGA Yazılım Hizmetleri',
    taxId:     '1234567890',  // ÖRNEK VKN — gerçeği ile değiştirin
    address:   Address::tr(
        city: 'Konya',
        district: 'Selçuklu',
        street: 'Yazılım Caddesi',
        buildingNumber: '42',
        postalCode: '42050',
    ),
    taxOffice: 'Selçuk',
    email:     'info@codega.com.tr',
    phone:     '+90 332 000 00 00',
    website:   'https://codega.com.tr',
);

// ── Alıcı: Örnek müşteri ────────────────────────────────────────
$customer = new Party(
    name:      'Örnek Müşteri A.Ş.',
    taxId:     '9876543210',
    address:   Address::tr(
        city: 'İstanbul',
        district: 'Kadıköy',
        street: 'Moda Caddesi',
        buildingNumber: '123',
    ),
    taxOffice: 'Kadıköy',
    email:     'muhasebe@ornekmusteri.com',
);

// ── Fatura satırları ────────────────────────────────────────────
$builder = (new UblBuilder())
    ->setProfile(UblBuilder::PROFILE_TEMEL)
    ->setInvoiceType(UblBuilder::TYPE_SATIS)
    ->setInvoiceNumber('COD' . date('Y') . '000000001')  // COD2026000000001
    ->setIssueDate(new \DateTimeImmutable())
    ->setCurrency('TRY')
    ->setSupplier($supplier)
    ->setCustomer($customer)
    ->setNotes('CODEGA ERP aylık abonelik ücretleri.')
    ->addLine(new InvoiceLine(
        id: 1,
        itemName: 'CodeGa ERP Pro Aylık Abonelik',
        quantity: 1,
        unitPrice: 1250.00,
        vatRate: 20.0,
        itemDescription: 'Nisan 2026 dönemi ERP kullanım lisansı',
    ))
    ->addLine(new InvoiceLine(
        id: 2,
        itemName: 'e-Fatura Entegrasyon Kurulum',
        quantity: 1,
        unitPrice: 750.00,
        vatRate: 20.0,
    ))
    ->addLine(new InvoiceLine(
        id: 3,
        itemName: 'Destek Paketi - Ek 10 Saat',
        quantity: 10,
        unitPrice: 350.00,
        vatRate: 20.0,
        unitCode: InvoiceLine::UNIT_SAAT,
        itemDescription: '2026-04 dönemi teknik destek saatleri',
    ));

// ── Özet ─────────────────────────────────────────────────────────
echo "═══════════════════════════════════════════════════════════\n";
echo "  UBL-TR 2.1 e-Fatura Üretim Örneği\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$summary = $builder->summary();
$numericKeys = ['line_count', 'line_total', 'tax_total', 'grand_total'];
foreach ($summary as $k => $v) {
    if (in_array($k, $numericKeys, true) && is_numeric($v)) {
        $display = number_format((float)$v, 2, ',', '.');
    } else {
        $display = (string)$v;
    }
    printf("  %-20s : %s\n", $k, $display);
}
echo "\n";

// ── XML üret ─────────────────────────────────────────────────────
$xml = $builder->build();

$outDir = __DIR__ . '/output';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);
$file = $outDir . '/01_basic_invoice.xml';
file_put_contents($file, $xml);

echo "✓ XML üretildi: $file\n";
echo "  Boyut: " . number_format(strlen($xml)) . " byte\n";
echo "  ETTN:  {$summary['ettn']}\n";
echo "\n";

// Temel validasyon: well-formed XML mi?
$dom = new DOMDocument();
libxml_use_internal_errors(true);
if (!$dom->loadXML($xml)) {
    echo "✗ XML hatalı formatta:\n";
    foreach (libxml_get_errors() as $err) {
        echo "  - {$err->message}";
    }
    exit(1);
}
echo "✓ Well-formed XML\n";

// Elementleri say
$xp = new DOMXPath($dom);
$xp->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
$xp->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
$lineCount = $xp->query('//cac:InvoiceLine')->length;
$partyCount = $xp->query('//cac:Party')->length;
echo "  Invoice satırları: $lineCount\n";
echo "  Party (taraf): $partyCount\n";

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  Sonraki Adımlar\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  1. Bu XML'i mali mühürle imzalamak için XAdESSigner bekleniyor\n";
echo "  2. İmzalı XML'i GİB test ortamına göndermek için GibClient bekleniyor\n";
echo "  3. Şimdilik XML'i açıp inceleyin: $file\n\n";
