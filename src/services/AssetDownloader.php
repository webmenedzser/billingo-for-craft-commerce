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

class AssetDownloader extends Component
{
    private $invoiceNumber;
    private $tempPath;
    private $tempFilePath;
    private $fileContent;
    private $filename;
    private $invoiceAssetVolume;
    private $invoiceAssetSubpath;

    /**
     * @var Asset
     */
    public $asset;

    /**
     * AssetDownloader constructor.
     *
     * @param $invoiceNumber
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function __construct($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->filename = $invoiceNumber . '.pdf';
        $this->tempPath = Craft::$app->path->getTempPath();
        $this->tempFilePath = $this->tempPath . $this->filename;
        $this->invoiceAssetVolume = Billingo::getInstance()->getSettings()->invoiceAssetVolume;
        $this->invoiceAssetSubpath = Billingo::getInstance()->getSettings()->invoiceAssetSubpath;

        parent::__construct();

        $this->downloadFile();
        $this->saveAsset();
    }

    /**
     * Download PDF from Billingo and save it to a temporary location.
     *
     * @return false|int
     * @throws \Exception
     */
    public function downloadFile()
    {
        $billingoService = new BillingoService();
        $this->fileContent = $billingoService->downloadInvoice($this->invoiceNumber);

        $result = file_put_contents($this->tempFilePath, $this->fileContent);

        if (!$result) {
            throw new \Exception('Error while downloading file from Billingo.');
        }

        Craft::info(
            'Invoice downloaded from Billingo for invoice nr. ' . $this->invoiceNumber,
            __METHOD__
        );

        return $result;
    }

    /**
     * Save asset to Craft volume if Volume is set in the Settings.
     *
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
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

        Craft::info(
            'Asset saved for invoice nr. ' . $this->invoiceNumber,
            __METHOD__
        );

        $this->asset = $asset;

        return true;
    }
}
