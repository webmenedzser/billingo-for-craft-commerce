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
use webmenedzser\billingo\services\PayloadService;
use webmenedzser\billingo\services\InvoiceElementService;
use webmenedzser\billingo\services\AssetDownloader;

use Craft;
use craft\base\Component;
use craft\commerce\elements\Order;

use Billingo\API\Connector\HTTP\Request;

/**
 * Class BillingoService
 *
 * @package webmenedzser\billingo\services
 */
class BillingoService extends Component
{
    // Public Methods
    // =========================================================================

    private $settings;
    private $billingo;
    private $order;
    private $client;
    private $invoice;
    private $payloadService;
    public $response = [];

    public function init(): void
    {
        $this->settings = Billingo::$plugin->getSettings();
        $this->billingo = new Request([
            'public_key' => Craft::parseEnv($this->settings->publicApiKey),
            'private_key' => Craft::parseEnv($this->settings->privateApiKey)
        ]);
        $this->payloadService = new PayloadService();
    }

    /**
     * Initiate Invoice creation process
     *
     * @param $orderId
     * @param int $refundAmount
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function createInvoice($orderId, $refundAmount = 0)
    {
        $this->order = Order::find()
            ->id($orderId)
            ->one();

        /**
         * Prevent billing if the selected Payment Method does not have Payment Method setting.
         */
        if (!$this->payloadService->gatewayInvoiceSettings($this->order)) {
            return null;
        }

        /**
         * Prepare Client data to send.
         */
        $this->client = $this->payloadService->createClientData($this->order);

        /**
         * Send Client data to Billingo API.
         */
        $this->response['client'] = $this->postClientData($this->client);

        /**
         * Prepare Invoice data to send.
         */
        $this->invoice = $this->payloadService->createInvoiceData($this->order, $this->response['client']['id'], $refundAmount);

        /**
         * Send Invoice data to Billingo API.
         */
        $this->response['invoice'] = $this->postInvoiceData($this->invoice);

        /**
         * Find invoice nr. to be stored.
         */
        $invoiceNumber = $this->response['invoice']['id'] ?? null;

        /**
         * Send e-mails from Billingo, if needed.
         */
        $this->sendEmailFromBillingo($invoiceNumber);

        /**
         * Initiate downloading invoice to Craft.
         */
        $assetDownloader = new AssetDownloader($invoiceNumber);
        $invoiceAssetId = $assetDownloader->asset->id ?? null;

        /**
         * Create InvoiceElement to store `orderId - invoiceNumber` pairs.
         */
        $invoiceElementService = new InvoiceElementService($this->order, $invoiceNumber, $invoiceAssetId);
        $invoiceElementService->createInvoiceElement();
    }

    /**
     * Storno invoice at Billingo
     *
     * @param $orderId
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function stornoInvoice($orderId)
    {
        /**
         * Find InvoiceElement row to get invoiceNumber to be stornoed.
         */
        $this->order = Order::find()
            ->id($orderId)
            ->one();

        /**
         * Create and initiate InvoiceElement deletion.
         */
        $invoiceElementService = new InvoiceElementService($this->order);
        if ($invoiceElementService) {
            $invoiceNumber = $invoiceElementService->deleteInvoiceElement();
        } else {
            Craft::error(
                'Could not find Invoice Element in database.',
                __METHOD__
            );
        }

        /**
         * If we had an invoiceNumber, start invoice stornoing at Billingo.
         */
        if ($invoiceNumber) {
            $this->removeInvoiceFromBillingo($invoiceNumber);
        }

        /**
         * If the refund was only a partial a refund, we have to
         * issue a new invoice with the reduced amount.
         *
         */
        if ($this->order->totalPaid > 0) {
            Craft::info(
                'Start over invoicing for order id. ' . $orderId,
                __METHOD__
            );

            $refundAmount = $this->order->total - $this->order->totalPaid;
            $this->createInvoice($orderId, $refundAmount);
        }
    }

    /**
     * Init Client data submission.
     *
     * @param $clientData
     * @return mixed
     * @throws \yii\base\ErrorException
     */
    public function postClientData($clientData)
    {
        return $this->postToBillingoApi('clients', $clientData);
    }

    /**
     * Init Invoice data submission.
     *
     * @param $invoiceData
     * @return mixed
     * @throws \yii\base\ErrorException
     */
    public function postInvoiceData($invoiceData)
    {
        return $this->postToBillingoApi('invoices', $invoiceData);
    }

    /**
     * Storno invoice in Billingo.
     */
    public function removeInvoiceFromBillingo($invoiceNumber)
    {
        Craft::info(
            'Start invoice storno for ID: ' . $invoiceNumber,
            __METHOD__
        );

        /**
         * Call the Billingo API.
         */
        $this->response = $this->getFromBillingoApi("invoices/{$invoiceNumber}/cancel");
        Craft::info(
            'Invoice should be stornoed for ID: ' . $invoiceNumber,
            __METHOD__
        );

        /**
         * Send e-mails from Billingo, if needed.
         */
        $stornoInvoiceNumber = $this->response['id'];
        if ($stornoInvoiceNumber) {
            $this->sendEmailFromBillingo($stornoInvoiceNumber);
        }

        Craft::info(
            'Storno invoice created, ID: ' . $stornoInvoiceNumber,
            __METHOD__
        );
    }

    /**
     * Trigger e-mail notification from Billingo.
     *
     * @param $invoiceNumber
     */
    public function sendEmailFromBillingo($invoiceNumber)
    {
        $triggerEmails = Billingo::getInstance()->getSettings()->triggerEmails;

        if ($triggerEmails) {
            Craft::info(
                'Sending triggered for ID: ' . $invoiceNumber,
                __METHOD__
            );

            $this->getFromBillingoApi("invoices/{$invoiceNumber}/send");

            Craft::info(
                'Sending finished for ID: ' . $invoiceNumber,
                __METHOD__
            );
        } else {
            Craft::info(
                Craft::t(
                    'billingo',
                    'Email triggering disabled.'
                ),
                __METHOD__
            );
        }
    }

    public function downloadInvoice($invoiceNumber)
    {
        Craft::info(
            'Downloading PDF for invoice "' . $invoiceNumber . '" from Billingo API endpoint.',
            __METHOD__
        );

        try {
            $response = $this->billingo->downloadInvoice($invoiceNumber);
        } catch (\yii\base\ErrorException $e) {
            $response['error'] = true;
            $response['errorMessage'] = $e->getMessage();

            Craft::error(
                print_r($response['errorMessage'], true),
                __METHOD__
            );
        }

        return $response->getBody()->getContents();
    }

    /**
     * Wrapper function to GET data from Billingo API.
     *
     * @param $endpoint
     * @return mixed
     */
    public function getFromBillingoApi($endpoint)
    {
        Craft::info(
            'Getting from Billingo "' . $endpoint . '" API endpoint.',
            __METHOD__
        );

        try {
            $response = $this->billingo->get($endpoint);
        } catch (\yii\base\ErrorException $e) {
            $response['error'] = true;
            $response['errorMessage'] = $e->getMessage();

            Craft::error(
                print_r($response['errorMessage'], true),
                __METHOD__
            );
        }

        Craft::info(
            'Info received from Billingo "' . $endpoint . '" API endpoint.',
            __METHOD__
        );

        return $response;
    }

    /**
     * Wrapper function to POST data to Billingo API.
     *
     * @param $endpoint
     * @param $payload
     * @return mixed
     * @throws \yii\base\ErrorException
     */
    public function postToBillingoApi($endpoint, $payload)
    {
        Craft::info(
            'Posting to Billingo "' . $endpoint . '" API endpoint.',
            __METHOD__
        );

        try {
            $response = $this->billingo->post($endpoint, $payload);
        } catch (\yii\base\ErrorException $e) {
            $response['error'] = true;
            $response['errorMessage'] = $e->getMessage();

            Craft::error(
                print_r($response['errorMessage'], true),
                __METHOD__
            );
        }

        Craft::info(
            'Posted to Billingo "' . $endpoint . '" API endpoint.',
            __METHOD__
        );

        return $response;
    }
}
