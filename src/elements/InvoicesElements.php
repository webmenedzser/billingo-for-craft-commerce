<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo\elements;

use webmenedzser\billingo\Billingo;
use webmenedzser\billingo\records\InvoicesRecords;
use webmenedzser\billingo\elements\db\InvoicesElementsQuery;

use Craft;
use craft\base\Element;
use craft\helpers\Template;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\commerce\elements\Order;

use yii\base\Exception;

/**
 * @author    Ottó Radics
 * @package   Billingo
 * @since     1.0.0
 */
class InvoicesElements extends Element
{
    // Constants
    // =========================================================================

    // Public Properties
    // =========================================================================

    /**
     * Order ID in Craft Commerce
     *
     * @var int
     */
    public $orderId;

    /**
     * @var int Invoice Number in Billingo
     */
    public $invoiceNumber;

    /**
     * @var int Invoice Asset ID in Craft
     */
    public $invoiceAssetId;

    /**
     * @var string Date when the invoice was stornoed.
     */
    public $dateStornoed;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('billingo', 'Invoice');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('billingo', 'Invoices');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new InvoicesElementsQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => 'all',
                'label' => Craft::t('billingo', 'All invoices'),
                'criteria' => [],
                'hasThumbs' => false
            ],
            [
                'key' => 'active',
                'label' => Craft::t('billingo', 'Active invoices'),
                'criteria' => [
                    'dateStornoed' => ':empty:'
                ],
                'hasThumbs' => false
            ],
            [
                'key' => 'stornoed',
                'label' => Craft::t('billingo', 'Stornoed invoices'),
                'criteria' => [
                    'dateStornoed' => ':notempty:'
                ],
                'hasThumbs' => false
            ]
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'invoiceNumber' => ['label' => Craft::t('billingo', 'Invoice Number')],
            'orderId' => ['label' => Craft::t('commerce', 'Order ID')],
            'invoiceAssetId' => ['label' => Craft::t('billingo', 'Invoice PDF')],
            'dateCreated' => ['label' => Craft::t('billingo', 'Invoice Date')],
            'dateStornoed' => ['label' => Craft::t('billingo', 'Storno Date')]
        ];

        return $attributes;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['orderId', 'invoiceNumber', 'invoiceAssetId'];
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'orderId':
                $order = Order::find()
                    ->id($this->orderId)
                    ->one();

                if ($order) {
                    return Template::raw('<a href="' . $order->getCpEditUrl() . '">' . $this->orderId . '</a>');
                }

                return '-';

            case 'invoiceNumber':
                return $this->invoiceNumber;

            case 'invoiceAssetId':
                if ($this->invoiceAssetId) {
                    $asset = Asset::find()
                        ->id($this->invoiceAssetId)
                        ->one();

                    Craft::info($attribute);

                    if (!$asset) {
                        return '-';
                    }

                    // Set this link a streamer download
                    return Template::raw('<a href="billingo/invoice/' . $asset->id . '/download">' . Craft::t("billingo", "Download Invoice") . '</a>');
                }

                return '-';

            case 'dateStornoed':
                if ($this->dateStornoed) {
                    return Craft::$app->formatter->asDate($this->dateStornoed);
                }
        }

        return parent::tableAttributeHtml($attribute);
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function extraFields(): array
    {
        $names = parent::extraFields();

        $names[] = 'orderId';
        $names[] = 'invoiceNumber';
        $names[] = 'invoiceAssetId';
        $names[] = 'dateCreated';
        $names[] = 'dateStornoed';

        return $names;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = [];

        $rules[] = [
            [
                'orderId',
                'invoiceNumber',
                'invoiceAssetId',
            ],
            'number',
            'integerOnly' => true
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getUiLabel(): string
    {
        return (string) $this->invoiceNumber;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?\craft\models\FieldLayout
    {
        return Craft::$app->fields->getLayoutByType(InvoicesElements::class);
    }

    /**
     * @inheritdoc
     */
    public function getGroup()
    {
        if ($this->groupId === null) {
            throw new InvalidConfigException('Tag is missing its group ID');
        }

        if (($group = Craft::$app->getTags()->getTagGroupById($this->groupId)) === null) {
            throw new InvalidConfigException('Invalid tag group ID: '.$this->groupId);
        }

        return $group;
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function getEditorHtml(): string
    {
        return false;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew): void
    {
        if (!$isNew) {
            $record = InvoicesRecords::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Invoice Element ID: ' . $this->id);
            }
        } else {
            $record = new InvoicesRecords();
            $record->id = $this->id;
        }

        $record->orderId = $this->orderId;
        $record->invoiceNumber = $this->invoiceNumber;
        $record->invoiceAssetId = $this->invoiceAssetId;
        $record->dateStornoed = $this->dateStornoed;

        $record->save(false);

        $this->id = $record->id;

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterMoveInStructure(int $structureId): void
    {
    }
}
