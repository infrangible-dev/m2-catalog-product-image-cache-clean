<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductImageCacheClean\Controller\Adminhtml\Clean;

use Infrangible\CatalogProductImageCacheClean\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product
    extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /** @var Data */
    protected $helper;

    public function __construct(Context $context, Builder $productBuilder, Data $helper)
    {
        parent::__construct($context, $productBuilder);

        $this->helper = $helper;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $productId = intval($this->getRequest()->getParam('id'));
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $storeId = intval($this->getRequest()->getParam('store'));

        $this->helper->cleanProductImageCache([$productId], $storeId);

        $this->messageManager->addSuccessMessage(__('The catalog product image cache was successfully cleaned'));

        $resultRedirect = $this->resultRedirectFactory->create();

        $urlParameters = ['id' => $productId];

        if ($productAttributeSetId > 0) {
            $urlParameters['set'] = $productAttributeSetId;
        }

        if ($storeId > 0) {
            $urlParameters['store'] = $storeId;
        }

        $resultRedirect->setPath('catalog/product/edit', $urlParameters);

        return $resultRedirect;
    }
}
