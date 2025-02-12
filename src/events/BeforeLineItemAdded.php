<?php

namespace webmenedzser\billingo\events;

use craft\events\CancelableEvent;

class BeforeLineItemAdded extends CancelableEvent
{
    public $lineItem;
}
