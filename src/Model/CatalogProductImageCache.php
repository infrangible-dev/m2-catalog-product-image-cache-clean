<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductImageCacheClean\Model;

use Infrangible\Core\Helper\Stores;
use Magento\Framework\Event\Manager;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductImageCache
{
    /** @var string */
    public const EVENT_ENTITY_IDS = 'entity_ids';

    /** @var string */
    public const EVENT_STORE_ID = 'store_id';

    /** @var string */
    public const EVENT_TEST = 'test';

    /** @var LoggerInterface */
    protected $logger;

    /** @var Stores */
    protected $storeHelper;

    /** @var Manager */
    protected $eventManager;

    /** @var bool */
    private $test = false;

    /** @var array */
    private $cacheEvents = [];

    /**
     * @param LoggerInterface $logger
     * @param Stores          $storeHelper
     * @param Manager         $eventManager
     */
    public function __construct(LoggerInterface $logger, Stores $storeHelper, Manager $eventManager)
    {
        $this->logger = $logger;
        $this->storeHelper = $storeHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * @return  bool
     */

    public function isTest(): bool
    {
        return $this->test === true;
    }

    /**
     * @param bool $test
     *
     * @return  void
     */

    public function setTest(bool $test = true)
    {
        $this->test = $test;
    }

    /**
     * @param int $entityId
     * @param int $storeId
     *
     * @throws \Exception
     */
    public function addCacheEvent(int $entityId, int $storeId)
    {
        if ($storeId == 0) {
            foreach ($this->storeHelper->getStores() as $store) {
                $storeId = $store->getId();
                if (is_string($storeId)) {
                    $storeId = intval($storeId);
                }
                $this->addCacheEvent($entityId, $storeId);
            }

            return;
        }

        if (!array_key_exists($storeId, $this->cacheEvents)) {
            $this->cacheEvents[$storeId] = [];
        }

        if (!in_array($entityId, $this->cacheEvents[$storeId])) {
            $this->cacheEvents[$storeId][] = $entityId;

            $this->logger->debug(
                sprintf(
                    'Article with id: %d in store with id: %d requires catalog product image cache change',
                    $entityId,
                    $storeId
                )
            );
        }
    }

    /**
     * Process all cache events.
     */
    public function clean()
    {
        foreach ($this->cacheEvents as $storeId => $entityIds) {
            $this->logger->debug(
                sprintf(
                    'Processing cache cleaning for store with id: %d and %d entity(ies)',
                    $storeId,
                    count($entityIds)
                )
            );

            $this->eventManager->dispatch('catalog_product_image_cache_clean', [
                static::EVENT_STORE_ID   => $storeId,
                static::EVENT_ENTITY_IDS => $entityIds,
                static::EVENT_TEST       => $this->isTest()
            ]);
        }
    }
}
