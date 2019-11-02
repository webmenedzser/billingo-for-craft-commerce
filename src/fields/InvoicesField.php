<?php

namespace webmenedzser\billingo\fields;

use craft\base\ElementInterface;
use webmenedzser\billingo\elements\db\InvoicesElementsQuery;
use webmenedzser\billingo\elements\InvoicesElements;

use Craft;
use craft\fields\BaseRelationField;

class InvoicesField extends BaseRelationField
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->allowLargeThumbsView = false;
        $this->allowLimit = false;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('billingo', 'Billingo Invoice');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('billingo', 'Select an invoice');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return InvoicesElements::class;
    }
}
