<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

/**
 * @author    Ottó Radics
 * @package   Billingo
 * @since     1.0.0
 */
return [
    'Billingo for Craft Commerce plugin loaded' => 'Billingo for Craft Commerce plugin betöltve',
    'Refund' => 'Visszatérítés',
    'Email triggering disabled.' => 'E-mail küldés kérése kikapcsolva.',
    'Proforma Invoice' => 'Proforma számla',
    'Normal Invoice' => 'Normál számla',
    'Invoice Number' => 'Számla sorszáma',
    'Payment Method mappings' => 'Fizetési módok párosítása',
    'Map your Payment Methods to [Billingo Payment Methods](https://billingo.readthedocs.io/en/latest/payment_methods/). You can override some of the settings if that payment method is used.' => 'Párosítsd a fizetési módokat a [Billingo fizetési módokhoz](https://billingo.readthedocs.io/en/latest/payment_methods/). A fizetési módra korlátozva bizonyos beállításokat felülírhatsz.',
    'Payment Gateways' => 'Fizetési módok',
    'Billingo Payment Method' => 'Billingo fizetési mód',
    'Payment Due (days)' => 'Fizetés határideje (nap)',
    'Invoice Type' => 'Számla típusa',
    'Select an invoice type you want to create once the order is marked as Paid.' => 'Válaszd ki, milyen számla típus jöjjön létre a rendelés fizetettre állításakor.',
    'Invoice Volume' => 'Számla Kötet',
    'Where do you want to store Invoices? Note that the subfolder path can contain variables like <code>{postDate}</code>.' => 'Hol szeretnéd tárolni a Számláidat? Az almappa útvonal változókat (mint amilyen a <code>{postDate}</code>) is tartalmazhat.',
    'Unit Type' => 'Egység típus',
    'Unit type for sold items. Set it to anything (pieces, puppies, etc.) you want.' => 'Az értékesített elemek egység típusa. Bármilyen szövegre állítható (darab, kutyus, stb.)',
    'Block UID' => 'Számlatömb UID',
    'Enter the block UID to the invoice block ID you want to create invoice in.' => 'Add meg annak a számlatömbnek a UID-ját, melyben a számlákat létre szeretnéd hozni.',
    'Round To' => 'Kerekítés',
    'Optional, defaults to 0 (no rounding).' => 'Opcionális, alapértelmezetten 0 (nincs kerekítés).',
    'Default VAT' => 'Alapértelmezett ÁFA',
    'This is the default VAT used in your store. Used as a fallback only, if no suitable VAT ID was found.' => 'Ez az alapértelmezett ÁFA mérték a shopban. Csak fallback-ként használjuk, ha nem található ÁFA ID.',
    'Trigger E-mails?' => 'E-mailek küldése?',
    'Set this to Yes if you want Commerce to trigger e-mail sending in Billingo.' => 'Állítsd Igen-re, ha azt szeretnéd, hogy a Billingo küldjön számlaértesítő e-maileket.',
    'Electronic Invoices' => 'Elektronikus számlák',
    'Invoice Language' => 'Számla nyelve',
    'Select language used for invoice templates here.' => 'Válaszd ki, milyen nyelvű számlák kerüljenek kiállításra.',
    'Select a default payment method.' => 'Válassz alapértelmezett fizetési módot.',
    'Public API Key' => 'Publikus API kulcs',
    'Enter Billingo public API key here.' => 'Publikus Billingo API kulcs megadása ebben a mezőben.',
    'Private API Key' => 'Titkos API kulcs',
    'Enter Billingo private API key here.' => 'Titkos Billingo API kulcs megadása ebben a mezőben.',
    'Plugin Name' => 'Plugin elnevezése',
    'Override plugin name.' => 'A plugin nevének felülírása (pl. a bal oldali menüben).',

    'Hungarian' => 'Magyar',
    'English' => 'Angol',
    'German' => 'Német',
    'French' => 'Francia',
    'Croatian' => 'Horvát',
    'Italian' => 'Olasz',
    'Romanian' => 'Román',
    'Slovak' => 'Szlovák',

    'Yes' => 'Igen',
    'No' => 'Nem',

    'Wiretransfer' => 'Átutalás',
    'Cash on Delivery' => 'Utánvét',
    'Cash' => 'Készpénz',
    'Bankcard' => 'Bankkártya',
    'SZEP card' => 'SZÉP kártya',
    'PayPal' => 'PayPal',
    'Postal check' => 'Postai csekk',
    'Compensation' => 'Visszatérítés',
    'Health insurance card' => 'Egészségkártya',
    'Coupon' => 'Kupon',
    'Voucher' => 'Utalvány',

    'Creating Invoice in Billingo.' => 'Számla létrehozása a Billingo-ban.',
    'Downloading invoice PDF from Billingo.' => 'Számla PDF letöltése Billingo-ból.',
    'Stornoing Invoice in Billingo.' => 'Számla sztornózása Billingo-ban.',

    'Shipping: ' => 'Szállítás: ',
    'Discount: ' => 'Kedvezmény: '
];
