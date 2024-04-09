<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductImageCacheClean\Observer;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductImageCacheClean\Helper\Data;
use Infrangible\CatalogProductImageCacheClean\Model\CatalogProductImageCache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductImageCacheClean
    implements ObserverInterface
{
    /** @var Variables */
    protected $variables;

    /** @var Data */
    protected $helper;

    /**
     * @param Variables $variables
     * @param Data      $helper
     */
    public function __construct(Variables $variables, Data $helper)
    {
        $this->variables = $variables;
        $this->helper = $helper;
    }

    /**
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        $entityIds = $event->getData(CatalogProductImageCache::EVENT_ENTITY_IDS);
        $storeId = intval($event->getData(CatalogProductImageCache::EVENT_STORE_ID));
        $isTest = boolval($event->getData(CatalogProductImageCache::EVENT_TEST));

        if (!$this->variables->isEmpty($entityIds)) {
            $this->helper->cleanProductImageCache($entityIds, $storeId, $isTest);
        }
    }
}
