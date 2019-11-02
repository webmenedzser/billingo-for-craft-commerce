<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * This plugin enables Craft to generate electronic or traditional invoices automatically with Billingo.
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo\models;

use craft\validators\ArrayValidator;
use webmenedzser\billingo\Billingo;

use Craft;
use craft\base\Model;

/**
 * Billingo Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Ottó Radics
 * @package   Billingo
 * @since     1.0.0
 *
 * @property string $publicApiKey
 * @property string $privateApiKey
 * @property string $templateLangCode
 * @property int $electronicInvoice
 * @property int $blockUid
 * @property int $roundTo
 *
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $pluginName = 'Billingo';

    /**
     * @var string
     */
    public $publicApiKey = '';

    /**
     * @var string
     */
    public $privateApiKey = '';

    /**
     * Template language code
     *
     * @var string
     */
    public $templateLangCode = '';

    /**
     * Create electronic invoices or not
     * Yes: 1
     * No: 0
     *
     * @var int
     */
    public $electronicInvoice = 1;

    /**
     * ID of "invoice block" in Billingo account
     * Important: The block_uid parameter should always be 0 or the chosen invoice block's ID!
     *
     * @link https://billingo.readthedocs.io/en/latest/invoices/#save-a-new-invoice
     *
     * @var int
     */
    public $blockUid = 0;

    /**
     * Invoice types:
     *   1: Proforma invoice
     *   3: Normal invoice
     *
     * @var int
     */
    public $invoiceType = 3;

    /**
     * Default payment method.
     *
     * @var int
     */
    public $paymentMethod;

    /**
     * Round total to.
     * Note: round_to parameter is optional. One of [0, 1, 5, 10], defaults to 0 -> no rounding.
     *
     * @link https://billingo.readthedocs.io/en/latest/invoices/#save-a-new-invoice
     *
     * @var int
     */
    public $roundTo = 0;

    /**
     * Set to true if you want Commerce to trigger e-mail sending in Billingo.
     *
     * @var int
     */
    public $triggerEmails = 1;

    /**
     * Unit type for sold items. Set it to anything you want.
     *
     * @var string
     */
    public $unitType = 'pc';

    /**
     * This is the default VAT used in your store.
     *
     * Possible VAT values:
     *   1 - 27% - ÁFA
     *   2 - 5%
     *   3 - 18%
     *   4 - 0% - AM
     *   5 - 0% - EU
     *
     * @var int
     */
    public $defaultVat = 1;

    /**
     * ID of the Asset Volume.
     *
     * @var int
     */
    public $invoiceAssetVolume;

    /**
     * Subpath for Invoices.
     *
     * @var string
     */
    public $invoiceAssetSubpath = '';

    /**
     * Setting array for payment methods.
     *
     * @var array
     */
    public $paymentMethodSettings = [];

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'pluginName',
                    'publicApiKey',
                    'privateApiKey',
                    'templateLangCode',
                    'unitType'
                ],
                'string'
            ],
            [
                [
                    'electronicInvoice',
                    'blockUid',
                    'invoiceType',
                    'roundTo',
                    'triggerEmails',
                    'defaultVat',
                    'invoiceAssetVolume',
                    'paymentMethod'
                ],
                'integer'
            ],
            [
                [
                    'paymentMethodSettings'
                ],
                ArrayValidator::class
            ],
            [
                [
                    'publicApiKey',
                    'privateApiKey',
                    'unitType',
                    'invoiceAssetSubpath'
                ],
                'default',
                'value' => ''
            ],
            [
                [
                    'electronicInvoice',
                    'defaultVat'
                ],
                'default',
                'value' => 1
            ],
            [
                [
                    'blockUid',
                    'roundTo',
                ],
                'default',
                'value' => 0
            ],
            [
                [
                    'triggerEmails'
                ],
                'default',
                'value' => 1
            ],
            [
                [
                    'invoiceType'
                ],
                'default',
                'value' => 3
            ],
            [
                [
                    'publicApiKey',
                    'privateApiKey'
                ],
                'required'
            ],
        ];
    }
}
