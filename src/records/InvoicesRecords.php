<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo\records;

use webmenedzser\billingo\Billingo;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Ottó Radics
 * @package   Billingo
 * @since     1.0.0
 */
class InvoicesRecords extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%billingo_invoicesrecords}}';
    }
}
