<?php

namespace webmenedzser\billingo\events;

use craft\events\CancelableEvent;

class AfterInvoiceDataCreated extends CancelableEvent
{
    public $invoiceData;
    public $order;
}
