<?php

namespace webmenedzser\billingo\jobs;

use webmenedzser\billingo\services\BillingoService;

use Craft;
use craft\queue\BaseJob;

class StornoInvoiceJob extends BaseJob
{
    public $orderId;

    // Public Methods
    // =========================================================================

    /**
     * When the Queue is ready to run your job, it will call this method.
     * You don't need any steps or any other special logic handling, just do the
     * jobs that needs to be done here.
     *
     * More info: https://github.com/yiisoft/yii2-queue
     */
    public function execute($queue): void
    {
        $billingoService = new BillingoService();
        $billingoService->stornoInvoice($this->orderId);
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns a default description for [[getDescription()]], if [[description]] isn’t set.
     *
     * @return string The default task description
     */
    protected function defaultDescription(): string
    {
        return Craft::t('billingo', 'Stornoing Invoice in Billingo.');
    }
}
