<?php
namespace webmenedzser\billingo\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use webmenedzser\billingo\elements\InvoicesElements;

class InvoicesElementsQuery extends ElementQuery
{
    public $orderId;
    public $invoiceNumber;
    public $invoiceAssetId;
    public $dateCreated;
    public $dateStornoed;

    public function orderId($value)
    {
        $this->orderId = $value;

        return $this;
    }

    public function invoiceNumber($value)
    {
        $this->invoiceNumber = $value;

        return $this;
    }

    public function invoiceAssetId($value)
    {
        $this->invoiceAssetId = $value;

        return $this;
    }

    public function dateCreated($value)
    {
        $this->dateCreated = $value;

        return $this;
    }

    public function dateStornoed($value)
    {
        $this->dateStornoed = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('billingo_invoicesrecords');

        // select the orderId column
        $this->query->select([
            'billingo_invoicesrecords.orderId',
            'billingo_invoicesrecords.invoiceNumber',
            'billingo_invoicesrecords.invoiceAssetId',
            'billingo_invoicesrecords.dateCreated',
            'billingo_invoicesrecords.dateStornoed'
        ]);

        if ($this->orderId) {
            $this->subQuery->andWhere(Db::parseParam('billingo_invoicesrecords.orderId', $this->orderId));
        }

        if ($this->invoiceNumber) {
            $this->subQuery->andWhere(Db::parseParam('billingo_invoicesrecords.invoiceNumber', $this->invoiceNumber));
        }

        if ($this->invoiceAssetId) {
            $this->subQuery->andWhere(Db::parseParam('billingo_invoicesrecords.invoiceAssetId', $this->invoiceAssetId));
        }

        if ($this->dateCreated) {
            $this->subQuery->andWhere(Db::parseDateParam('billingo_invoicesrecords.dateCreated', $this->dateCreated));
        }

        if ($this->dateStornoed) {
            $this->subQuery->andWhere(Db::parseDateParam('billingo_invoicesrecords.dateStornoed', $this->dateStornoed));
        }

        return parent::beforePrepare();
    }
}
