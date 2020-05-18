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
use webmenedzser\billingo\models\Settings;

use Craft;
use craft\base\Component;
use craft\commerce\records\TaxRate;
use craft\commerce\models\LineItem;

/**
 * Class PayloadService
 *
 * @package webmenedzser\billingo\services
 */
class PayloadService extends Component
{
    private $clientData;
    private $invoiceData;

    /**
     * Create Client data from Order.
     *
     * @param $order
     * @return array
     * @throws \yii\base\ErrorException
     */
    public function createClientData($order)
    {
        Craft::info(
            'Creating Client data started.',
            __METHOD__
        );

        $this->clientData = [
            "name" => $order->billingAddress->businessName ? $order->billingAddress->businessName : $order->billingAddress->fullName,
            "email" => $order->customer->email,
            "phone" => $order->billingAddress->phone,
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
                // TODO: wait for reply from Billingo
                "country" => $order->billingAddress->countryText,
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
    public function createInvoiceData($order, $clientId, $refundAmount = 0)
    {
        $lineItems = $this->createItemsArray($order);
        $shippingItem = $this->createShippingItem($order);

        $items = array_merge($lineItems, $shippingItem);

        if ($refundAmount) {
            $refundItem = $this->createRefundItem($refundAmount);
            $items = array_merge($items, $refundItem);
        }

        Craft::info(
            'Creating Invoice data started.',
            __METHOD__
        );

        $gatewayInvoiceSettings = $this->gatewayInvoiceSettings($order);
        if ($gatewayInvoiceSettings['dueDays'] > 0) {
            $dueDate = date('Y-m-d', strtotime(date('Y-m-d'). '+ ' . $gatewayInvoiceSettings['dueDays'] . ' days'));
        }

        $this->invoiceData = [
            'fulfillment_date' => date('Y-m-d'),
            'due_date' => (string) ($dueDate ?? date('Y-m-d')),
            'payment_method' => (int) ($gatewayInvoiceSettings['billingoPaymentMethodId'] ? $gatewayInvoiceSettings['billingoPaymentMethodId'] : Billingo::getInstance()->getSettings()->paymentMethod),
            'comment' => $order->message ?? '',
            'template_lang_code' => (string) Billingo::getInstance()->getSettings()->templateLangCode,
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

            $items[] = [
                'description' => (string) $lineItem->description,
                'qty' => (float) $lineItem->qty,
                'net_unit_price' => $lineItem->salePrice ? (float) $lineItem->salePrice : (float) $lineItem->price,
                'vat_id' => $this->determineVatId($rateAsPercent),
                'unit' => Billingo::getInstance()->getSettings()->unitType,
                'item_comment' => (string) $lineItem->sku
            ];
        }

        return $items;
    }

    /**
     * Create a Billingo lineItem for Shipping costs
     *
     * @param $order
     * @return array
     */
    public function createShippingItem($order)
    {
        $shipping = [];
        $shippingMethod = $order->shippingMethod;
        $adjustments = $order->adjustments;
        $shippingTaxRate = null;

        foreach ($adjustments as $adjustment) {
            $snapshot = $adjustment->getSourceSnapshot();
            $taxable = $snapshot['taxable'] ?? '';

            if ($taxable === 'order_total_shipping') {
                $shippingTaxRate = $snapshot['rate'] * 100 . '%';
            }
        }

        $shipping[] = [
            'description' => $shippingMethod ? $shippingMethod->name : Craft::t('commerce', 'Shipping'),
            'qty' => 1.0,
            'net_unit_price' => (float) $order->getTotalShippingCost(),
            'vat_id' => $this->determineVatId($shippingTaxRate),
            'unit' => Billingo::getInstance()->getSettings()->unitType
        ];

        return $shipping;
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
            'vat_id' => $this->determineVatId(),
            'unit' => Billingo::getInstance()->getSettings()->unitType
        ];

        return $refund;
    }

    public function determineVatId($rateAsPercent = null)
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
    }
}
