<?php


namespace webmenedzser\billingo\services;

use craft\helpers\DateTimeHelper;
use webmenedzser\billingo\records\InvoicesRecords;
use webmenedzser\billingo\elements\InvoicesElements;

use Craft;
use craft\commerce\elements\Order;

class InvoiceElementService
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * @var int
     */
    private $invoiceAssetId;

    /**
     * @var invoiceRecord
     */
    private $invoiceRecord;

    public function __construct($order, $invoiceNumber = null, $invoiceAssetId = null)
    {
        $this->order = $order;

        if ($invoiceNumber) {
            $this->invoiceNumber = $invoiceNumber;
        }

        if ($invoiceAssetId) {
            $this->invoiceAssetId = $invoiceAssetId;
        }
    }

    public function createInvoiceElement()
    {
        // Create and populate the entry
        $invoiceElement = new InvoicesElements([
            'orderId' => $this->order->id,
            'invoiceNumber' => $this->invoiceNumber,
            'invoiceAssetId' => $this->invoiceAssetId,
        ]);

        Craft::$app->elements->saveElement($invoiceElement, false, false);

        Craft::info(
            'InvoiceElement saved.',
            __METHOD__
        );

        return $invoiceElement;
    }

    public function deleteInvoiceElement()
    {
        $invoiceElement = InvoicesElements::find()
            ->orderId($this->order->id)
            ->one();

        if ($invoiceElement) {
            $invoiceId = $invoiceElement->invoiceNumber;
            $date = date('U');
            $invoiceElement->dateStornoed = DateTimeHelper::toDateTime($date);

            Craft::$app->elements->saveElement($invoiceElement);

            Craft::info(
                'InvoiceElement stornoed.',
                __METHOD__
            );

            return $invoiceId;
        }

        return null;
    }
}
