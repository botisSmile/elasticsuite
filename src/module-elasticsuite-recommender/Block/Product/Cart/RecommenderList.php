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

namespace Smile\ElasticsuiteRecommender\Block\Product\Cart;

use Smile\ElasticsuiteRecommender\Block\Product\RecommenderList as GenericRecommenderList;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Cart specific recommender block.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 */
class RecommenderList extends GenericRecommenderList
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var ProductInterface|false
     */
    private $lastAddedProduct;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context               $context Block context.
     * @param \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model   Recommender model.
     * @param \Smile\ElasticsuiteRecommender\Helper\Data           $helper  Data helper.
     * @param \Magento\Checkout\Model\Session                      $session Checkout session.
     * @param array                                                $data    Additional block data.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model,
        \Smile\ElasticsuiteRecommender\Helper\Data $helper,
        \Magento\Checkout\Model\Session $session,
        array $data = []
    ) {
        parent::__construct($context, $model, $helper, $data);
        $this->checkoutSession = $session;
        $this->lastAddedProduct = null;
    }

    /**
     * Get recommended items.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getItems()
    {
        return $this->getAllItems();
    }

    /**
     * Returns the product to use for recommendations
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->getLastAddedProduct();
    }

    /**
     * Retrieve just added to cart product object
     *
     * @return ProductInterface|false|null
     */
    private function getLastAddedProduct()
    {
        if ($this->lastAddedProduct === null) {
            $this->lastAddedProduct = false;
            try {
                $itemsCollection = $this->checkoutSession->getQuote()->getItemsCollection()->addOrder('created_at');
                foreach ($itemsCollection as $item) {
                    // Only grab last added visible item.
                    if (!$item->isDeleted() && !$item->getParentItemId() && !$item->getParentItem()) {
                        $this->lastAddedProduct = $item->getProduct();
                    }
                }
            } catch (NoSuchEntityException $e) {
                ;
            }
        }

        return $this->lastAddedProduct;
    }
}
