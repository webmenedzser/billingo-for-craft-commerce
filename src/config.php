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
 * Billingo config.php
 *
 * This file exists only as a template for the Billingo plugin for Craft Commerce settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'billingo.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    '*' => [
        'pluginName' => 'Billingo',
        'publicApiKey' => '',
        'privateApiKey' => '',
        'templateLangCode' => 'en',
        'electronicInvoice' => 1,
        'blockUid' => 0,
        'invoiceType' => 3,
        'roundTo' => 0,
        'triggerEmails' => 1,
        'unitType' => 'pc',
        'defaultVat' => 1,
        'invoiceAssetVolume' => 0,
        'invoiceAssetSubpath' => '',
    ]
];
