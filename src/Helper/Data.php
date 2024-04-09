<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductImageCacheClean\Helper;

use FeWeDev\Base\Arrays;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\Export;
use Infrangible\Core\Helper\Files;
use Infrangible\Core\Helper\FileSystem;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\CacheInterface;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var LoggerInterface */
    protected $logging;

    /** @var Export */
    protected $exportHelper;

    /** @var Database */
    protected $databaseHelper;

    /** @var Config */
    protected $catalogProductMediaConfig;

    /** @var Files */
    protected $files;

    /** @var FileSystem */
    protected $fileSystem;

    /** @var Arrays */
    protected $arrays;

    /** @var CacheInterface */
    protected $cacheManager;

    /**
     * @param LoggerInterface $logging
     * @param Export          $exportHelper
     * @param Database        $databaseHelper
     * @param Config          $catalogProductMediaConfig
     * @param Files           $files
     * @param FileSystem      $fileSystem
     * @param Arrays          $arrays
     * @param CacheInterface  $cacheManager
     */
    public function __construct(
        LoggerInterface $logging,
        Export $exportHelper,
        Database $databaseHelper,
        Config $catalogProductMediaConfig,
        Files $files,
        FileSystem $fileSystem,
        Arrays $arrays,
        CacheInterface $cacheManager
    ) {
        $this->logging = $logging;
        $this->exportHelper = $exportHelper;
        $this->databaseHelper = $databaseHelper;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->files = $files;
        $this->fileSystem = $fileSystem;
        $this->arrays = $arrays;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @throws \Exception
     */
    public function cleanProductImageCache(array $entityIds, int $storeId, bool $isTest = false): void
    {
        $entityIds = array_unique($entityIds);

        if (count($entityIds) === 0) {
            return;
        }

        $dbAdapter = $this->databaseHelper->getDefaultConnection();

        $this->logging->debug(sprintf('Cleaning image cache entries of %d articles', count($entityIds)));

        $directory =
            $this->fileSystem->getMediaPath(sprintf('%s/cache', $this->catalogProductMediaConfig->getBaseMediaPath()));

        $cacheDirectories = $this->files->determineDirectoriesFromFilePath($directory);

        $galleryImages = $this->exportHelper->getGalleryImages($dbAdapter, $entityIds, $storeId);

        foreach ($galleryImages as $entityGalleryImages) {
            foreach ($entityGalleryImages as $entityGalleryImage) {
                $value = $this->arrays->getValue($entityGalleryImage, 'value');

                foreach ($cacheDirectories as $cacheDirectory) {
                    $galleryImageFileName = sprintf('%s/%s', rtrim($cacheDirectory, '/'), ltrim($value, '/'));

                    if (file_exists($galleryImageFileName)) {
                        $this->logging->debug(sprintf('Deleting cache image: %s', $galleryImageFileName));

                        if (!$isTest) {
                            @unlink($galleryImageFileName);
                        }

                        $imagePath = sprintf('IMG_INFO%s', $galleryImageFileName);
                        $this->cacheManager->remove($imagePath);
                    }
                }
            }
        }

        $this->logging->info(sprintf('Cleaned image cache entries of %d articles', count($entityIds)));
    }
}
