<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo\services;

use webmenedzser\billingo\Billingo;
use webmenedzser\billingo\events\AfterInvoiceDataCreated;
use webmenedzser\billingo\events\BeforeAdjustmentAdded;
use webmenedzser\billingo\events\BeforeLineItemAdded;
use webmenedzser\billingo\models\Settings;

use Craft;
use craft\base\Component;
use craft\commerce\records\TaxRate;
use craft\commerce\models\LineItem;
use craft\commerce\elements\Order;

/**
 * Class PayloadService
 *
 * @package webmenedzser\billingo\services
 */
class PayloadService extends Component
{
    private $clientData;
    private $invoiceData;

    public const EVENT_BEFORE_LINE_ITEM_ADDED = 'onBeforeLineItemAdded';
    public const EVENT_BEFORE_ADJUSTMENT_ADDED = 'onBeforeAdjustmentAdded';
    public const EVENT_AFTER_INVOICE_DATA_CREATED = 'onAfterInvoiceDataCreated';

    /**
     * Create Client data from Order.
     *
     * @param $order
     * @return array
     * @throws \yii\base\ErrorException
     */
    public function createClientData(Order $order)
    {
        Craft::info(
            'Creating Client data started.',
            __METHOD__
        );

        $fullName = $order->billingAddress->fullName ?: ($order->billingAddress->lastName . ' ' . $order->billingAddress->firstName);

        $this->clientData = [
            "name" => $order->billingAddress->businessName ?: $fullName,
            "email" => $order->customer->email,
            "phone" => $order->billingAddress->phone,
            "taxcode" => $order->billingAddress->businessTaxId ? $order->billingAddress->businessTaxId : '',
            "billing_address" => [
                "street_name" => $order->billingAddress->address1 . ' ' . $order->billingAddress->address2,
                "street_type" => '',
                "house_nr" => '',
                "block" => '',
                "entrance" => '',
                "floor" => '',
                "door" => '',
                "city" => $order->billingAddress->city,
                "postcode" => $order->billingAddress->zipCode,
                "country" => CountryService::getCountryNameByCode($order->billingAddress->countryIso),
                "district" => ''
            ]
        ];

        Craft::info(
            'Creating Client data ended.',
            __METHOD__
        );

        return $this->clientData;
    }

    /**
     * Create Invoice data from Order.
     *
     * @param $order
     * @return array
     */
    public function createInvoiceData(Order $order, $clientId, $refundAmount = 0)
    {
        $lineItems = $this->createItemsArray($order);
        $adjustments = $this->createShippingAndDiscountAdjustmentItems($order);
        $items = array_merge($lineItems, $adjustments);

        if ($refundAmount) {
            $refundItem = $this->createRefundItem($refundAmount);
            $items = array_merge($items, $refundItem);
        }
        if (!count($items)) {
            Craft::info(
                'There are no lineItems for this invoice.',
                __METHOD__
            );

            return null;
        }

        Craft::info(
            'Creating Invoice data started.',
            __METHOD__
        );

        $gatewayInvoiceSettings = $this->gatewayInvoiceSettings($order);
        if ($gatewayInvoiceSettings['dueDays'] > 0) {
            $dueDate = date('Y-m-d', strtotime(date('Y-m-d'). '+ ' . $gatewayInvoiceSettings['dueDays'] . ' days'));
        }

        $language = Billingo::getInstance()->getSettings()->templateLangCode;
        $comment = Craft::t(
            'billingo',
            'Order reference: {orderReference}',
            ['orderReference' => $order->reference],
            $language
        ) . PHP_EOL;
        if ($order->message) {
            $comment .= $order->message;
        }

        $this->invoiceData = [
            'fulfillment_date' => date('Y-m-d'),
            'due_date' => (string) ($dueDate ?? date('Y-m-d')),
            'payment_method' => (int) ($gatewayInvoiceSettings['billingoPaymentMethodId'] ? $gatewayInvoiceSettings['billingoPaymentMethodId'] : Billingo::getInstance()->getSettings()->paymentMethod),
            'comment' => $comment,
            'template_lang_code' => (string) $language,
            'electronic_invoice' => (int) Billingo::getInstance()->getSettings()->electronicInvoice,
            'currency' => $order->paymentCurrency,
            'client_uid' => (int) $clientId,
            'block_uid' => (int) Billingo::getInstance()->getSettings()->blockUid,
            'type' => (int) ($gatewayInvoiceSettings['invoiceType'] ? $gatewayInvoiceSettings['invoiceType'] : Billingo::getInstance()->getSettings()->invoiceType),
            'round_to' => (int) Billingo::getInstance()->getSettings()->roundTo,
            'items' => $items,
        ];

        Craft::info(
            'Creating Invoice data ended.',
            __METHOD__
        );

        $event = new AfterInvoiceDataCreated([
            'invoiceData' => $this->invoiceData,
            'order' => $order
        ]);
        $this->trigger(self::EVENT_AFTER_INVOICE_DATA_CREATED, $event);
        $this->invoiceData = $event->invoiceData;

        return $this->invoiceData;
    }

    /**
     * Generate an `items` array from the order lineItems
     *
     * @param $order
     * @return array
     */
    public function createItemsArray($order)
    {
        $items = [];

        foreach ($order->lineItems as $lineItem) {
            $rateAsPercent = $lineItem->taxCategory->taxRates[0]->rateAsPercent;
            $event = new BeforeLineItemAdded([
                'lineItem' => $lineItem
            ]);
            $this->trigger(self::EVENT_BEFORE_LINE_ITEM_ADDED, $event);

            if ($event->isValid) {
                $items[] = [
                    'description' => (string) $lineItem->description,
                    'qty' => (float) $lineItem->qty,
                    'net_unit_price' => $lineItem->salePrice ? (float) $lineItem->salePrice : (float) $lineItem->price,
                    'vat_id' => self::determineVatId($rateAsPercent),
                    'unit' => Billingo::getInstance()->getSettings()->unitType,
                    'item_comment' => (string) $lineItem->sku
                ];
            };
        }

        return $items;
    }

    /**
     * Create a Billingo lineItem for Refunds.
     *
     * @param $order
     * @return array
     */
    public function createRefundItem($refundAmount)
    {
        $refund = [];

        $refund[] = [
            'description' => Craft::t('commerce', 'Refund'),
            'qty' => 1.0,
            'net_unit_price' => (float) -$refundAmount,
            'vat_id' => self::determineVatId(),
            'unit' => Billingo::getInstance()->getSettings()->unitType
        ];

        return $refund;
    }

    /**
     * Create lineItems for shipping & discount adjustments.
     *
     * @param Order $order
     *
     * @return array
     */
    public function createShippingAndDiscountAdjustmentItems(Order $order)
    {
        $adjustments = [];
        $discountAdjustments = $order->getAdjustmentsByType('discount');
        $shippingAdjustments = $order->getAdjustmentsByType('shipping');

        foreach ($discountAdjustments as $adjustment) {
            $event = new BeforeAdjustmentAdded([
                'adjustment' => [
                    'description' => (string) $adjustment->name,
                    'item_comment' => (string) $adjustment->description,
                    'qty' => (float) 1.0,
                    'net_unit_price' => $adjustment->amount,
                    'vat_id' => self::determineVatId(),
                    'unit' => Billingo::getInstance()->getSettings()->unitType,
                ]
            ]);
            $this->trigger(self::EVENT_BEFORE_ADJUSTMENT_ADDED, $event);

            if ($event->isValid) {
                $adjustments[] = $event->adjustment;
            }
        }

        foreach ($shippingAdjustments as $adjustment) {
            $event = new BeforeAdjustmentAdded([
                'adjustment' => [
                    'description' => (string) $adjustment->name,
                    'item_comment' => (string) $adjustment->description,
                    'qty' => (float) 1.0,
                    'net_unit_price' => $adjustment->amount,
                    'vat_id' => self::determineVatId(),
                    'unit' => Billingo::getInstance()->getSettings()->unitType,
                ]
            ]);
            $this->trigger(self::EVENT_BEFORE_ADJUSTMENT_ADDED, $event);

            if ($event->isValid) {
                $adjustments[] = $event->adjustment;
            }
        }

        return $adjustments;
    }

    public static function determineVatId($rateAsPercent = null)
    {
        /**
         * Get Billingo setting for default VAT. In worst case we will return this.
         */
        $defaultVatId = Billingo::getInstance()->getSettings()->defaultVat;

        if (!$rateAsPercent) {
            return (int) $defaultVatId;
        }

        /**
         * Get tax rate for lineItem, as percent.
         */
        $tax = $rateAsPercent ?? '0%';

        /**
         * Possible VAT rates in Billingo.
         */
        $vats = [
            '1' => '27%',
            '2' => '5%',
            '3' => '18%',
            '4' => '0%',
        ];

        /**
         * NOTE: If Commerce is Lite edition, we can only have one VAT rate.
         */
        foreach ($vats as $vat_id => $vat_rate) {
            if ($tax == $vat_rate) {
                return (int) $vat_id;
            }
        }

        return (int) $defaultVatId;
    }

    /**
     * Determine payment method ID for Billingo.
     *
     * 2 - Wiretransfer
     * 4 - Cash on Delivery
     * 1 - Cash
     * 5 - Bankcard
     * 6 - SZEP card
     * 7 - PayPal
     * 8 - Postal check
     * 9 - Compensation
     * 10 - Health insurance card
     * 11 - Coupon
     * 12 - Voucher
     *
     * @param $order
     * @return int
     */
    public function gatewayInvoiceSettings($order)
    {
        $paymentMethodSettings = Billingo::getInstance()->getSettings()->paymentMethodSettings;

        if ($paymentMethodSettings) {
            foreach ($paymentMethodSettings as $paymentMethodSetting) {
                if ($paymentMethodSetting['paymentGatewayId'] === $order->gatewayId) {
                    return $paymentMethodSetting;
                }
            }
        }

        return null;
    }
}
