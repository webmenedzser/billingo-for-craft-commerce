<?php

namespace webmenedzser\billingo\events;

use craft\events\CancelableEvent;

class BeforeAdjustmentAdded extends CancelableEvent
{
    public $adjustment;
}
