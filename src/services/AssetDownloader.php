<?php
/**
 * Billingo plugin for Craft Commerce
 *
 * Integrate Craft Commerce with Billingo
 *
 * @link      https://www.webmenedzser.hu
 * @copyright Copyright (c) 2019 Ottó Radics
 */

namespace webmenedzser\billingo\services;

use webmenedzser\billingo\Billingo;
use webmenedzser\billingo\services\BillingoService;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\StringHelper;
use craft\services\Volumes;

use Otisz\BillingoConnector\Connector;

class AssetDownloader extends Component
{
    private $invoiceNumber;
    private $tempPath;
    private $tempFilePath;
    private $fileContent;
    private $filename;
    private $invoiceAssetVolume;
    private $invoiceAssetSubpath;

    public $assetId;

    public function __construct($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->filename = $invoiceNumber . '.pdf';
        $this->tempPath = Craft::$app->path->getTempPath();
        $this->tempFilePath = $this->tempPath . $this->filename;
        $this->invoiceAssetVolume = Billingo::getInstance()->getSettings()->invoiceAssetVolume;
        $this->invoiceAssetSubpath = Billingo::getInstance()->getSettings()->invoiceAssetSubpath;

        $this->downloadFile();
        $this->assetId = $this->saveAsset()->id;
    }

    public function downloadFile()
    {
        $billingoService = new BillingoService();
        $this->fileContent = $billingoService->downloadInvoice($this->invoiceNumber);

        $result = file_put_contents($this->tempFilePath, $this->fileContent);

        if (!$result) {
            throw new \Exception('Error while downloading file from Billingo.');
        }

        return $result;
    }

    public function saveAsset()
    {
        /**
         * Check if the volume set to store Invoices exists.
         */
        if (!($this->invoiceAssetVolume && Craft::$app->getVolumes()->getVolumeById($this->invoiceAssetVolume))) {
            return false;
        }

        $assets = Craft::$app->getAssets();

        // TODO: support for subpath field
        $folder = $assets->getRootFolderByVolumeId($this->invoiceAssetVolume);

        $asset = new Asset();
        $asset->title = StringHelper::toTitleCase(
            pathinfo($this->filename, PATHINFO_FILENAME)
        );
        $asset->tempFilePath = $this->tempFilePath;
        $asset->filename = $this->filename;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $this->invoiceAssetVolume;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        $result = Craft::$app->getElements()->saveElement($asset);

        // In case of error, let user know about it.
        if ($result === false) {
            throw new \Exception('Error while creating Asset.');
        }

        return $asset;
    }
}
