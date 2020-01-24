<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo;

use webmenedzser\billingo\fields\InvoicesField;
use webmenedzser\billingo\jobs\CreateInvoiceJob;
use webmenedzser\billingo\jobs\StornoInvoiceJob;
use webmenedzser\billingo\models\Settings;
use webmenedzser\billingo\elements\InvoicesElements;
use webmenedzser\billingo\services\BillingoService;
use webmenedzser\billingo\controllers\InvoiceDownloadController;

use Craft;
use craft\helpers\UrlHelper;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Plugins;
use craft\commerce\elements\Order;
use craft\commerce\events\TransactionEvent;
use craft\commerce\events\ProcessPaymentEvent;
use craft\commerce\services\Payments;
use craft\web\UrlManager;

use yii\base\Event;

/**
 * Class Billingo
 *
 * @author    Ottó Radics
 * @package   Billingo
 * @since     1.0.0
 *
 */
class Billingo extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Billingo
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $schemaVersion = '1.3.0';

    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_redirectAfterInstall();
        $this->_registerElementType();
        $this->_registerFieldType();
        $this->_registerCpRoutes();
        $this->_registerTransactionEvents();
        $this->_registerLogger();
    }

    public function getPluginName(): string
    {
        return Craft::t('billingo', $this->getSettings()->pluginName);
    }

    public function getCpNavItem()
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = $this->getPluginName() ? $this->getPluginName() : 'Billingo';

        // TODO: fix error on element index
        return $navItem;
    }

    // Protected Methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'billingo/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    protected function getCpRoutes(): array
    {
        return [
            'billingo/invoice/<assetId>/download' => 'billingo/invoice-download/serve'
        ];
    }

    private function _registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->getCpRoutes());
            }
        );
    }

    private function _redirectAfterInstall()
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this && !Craft::$app->getRequest()->isConsoleRequest ) {
                    Craft::$app->response->redirect(UrlHelper::cpUrl('settings/plugins/billingo'))->send();
                }
            }
        );
    }

    private function _registerElementType()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = InvoicesElements::class;
            }
        );
    }

    private function _registerFieldType()
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = InvoicesField::class;
        });
    }

    private function _registerTransactionEvents()
    {
        Event::on(
            Order::class,
            Order::EVENT_AFTER_ORDER_PAID,
            function(Event $e) {
                $orderId = $e->sender->id;

                Craft::$app->queue->push(new CreateInvoiceJob([
                    'orderId' => $orderId
                ]));
            }
        );

        Event::on(
            Payments::class,
            Payments::EVENT_AFTER_REFUND_TRANSACTION,
            function(TransactionEvent $e) {
                // @var Order $order
                $orderId = $e->transaction->order->id;

                Craft::$app->queue->push(new StornoInvoiceJob([
                    'orderId' => $orderId,
                ]));
            }
        );
    }

    private function _registerLogger()
    {
        // Create a new file target
        $fileTarget = new \craft\log\FileTarget([
            'logFile' => '@storage/logs/billingo.log',
            'categories' => ['webmenedzser\billingo\*']
        ]);

        // Add the new target file target to the dispatcher
        Craft::getLogger()->dispatcher->targets[] = $fileTarget;

    }
}
