<?php

namespace webmenedzser\billingo\migrations;

use Craft;
use craft\db\Migration;

/**
 * m191108_092514_dateStornoedColumnAdded migration.
 */
class m191108_104200_dateStornoedColumnAdded extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('billingo_invoicesrecords', 'dateStornoed', "datetime default NULL");

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('billingo_invoicesrecords', 'dateStornoed');

        return true;
    }
}
