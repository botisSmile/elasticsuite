<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Controller\Recommender;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Ajax upsell and related products renderer
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Ajax extends Action implements HttpPostActionInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Product repository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Ajax constructor.
     *
     * @param Context                    $context           Context.
     * @param Registry                   $coreRegistry      Core Registry.
     * @param ProductRepositoryInterface $productRepository Product repository.
     * @param StoreManagerInterface      $storeManager      Store manager interface.
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Render upsell and related products
     *
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $this->initProduct();

        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }

    /**
     * Initialize and check product
     *
     * @return void
     */
    private function initProduct()
    {
        $product = false;

        if ($productId = (int) $this->getRequest()->getParam('id')) {
            try {
                $product = $this->productRepository->getById($productId);

                if (!in_array($this->storeManager->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
                    throw new NoSuchEntityException();
                }

                if (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
                    throw new NoSuchEntityException();
                }
            } catch (NoSuchEntityException $noEntityException) {
                ;
            }
        }

        if ($product) {
            $this->coreRegistry->register('current_product', $product);
            $this->coreRegistry->register('product', $product);
        }
    }
}
