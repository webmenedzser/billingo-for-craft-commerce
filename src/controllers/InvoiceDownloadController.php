<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo\controllers;

use webmenedzser\billingo\Billingo;
use webmenedzser\billingo\jobs\AssetDownloadJob;
use webmenedzser\billingo\services\AssetDownloader;

use Craft;
use craft\elements\Asset;
use craft\web\Controller;
use craft\helpers\StringHelper;

use Otisz\BillingoConnector\Connector;

class InvoiceDownloadController extends Controller
{
    // Public Properties
    // =========================================================================
    public $allowAnonymous = true;

    public function actionServe($assetId)
    {
        $this->requireLogin();

        $asset = Asset::find()
            ->id($assetId)
            ->one();

        if (!$asset) {
            return false;
        }

        /**
         * Get absolute path for asset
         *
         * @link https://craftcms.stackexchange.com/a/26614/5905
         */
        $volumePath = $asset->getVolume()->settings['path'];
        $folderPath = $asset->getFolder()->path;

        $assetFilePath = \Yii::getAlias($volumePath) . $folderPath . '/' . $asset->filename;

        /**
         * Stream the file to the user
         */
        $response = Craft::$app->getResponse()->sendFile($assetFilePath);

        return $response;
    }
}
