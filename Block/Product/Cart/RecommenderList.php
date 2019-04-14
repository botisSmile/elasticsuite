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
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Block\Product\Cart;

use Smile\ElasticsuiteRecommender\Block\Product\RecommenderList as GenericRecommenderList;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterface|false
     */
    private $lastAddedProduct;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context               $context           Block context.
     * @param \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model             Recommender model.
     * @param \Magento\Checkout\Model\Session                      $session           Checkout session.
     * @param ProductRepositoryInterface                           $productRepository Product repository.
     * @param array                                                $data              Additional block data.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model,
        \Magento\Checkout\Model\Session $session,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $model, $data);
        $this->checkoutSession = $session;
        $this->productRepository = $productRepository;
        $this->lastAddedProduct = null;
    }

    /**
     * Get recommended items.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getAllItems()
    {
        $lastAddedProduct = $this->getLastAddedProduct();
        if ($lastAddedProduct) {
            return parent::getAllItems();
        }

        return [];
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
            $productId = $this->getLastAddedProductId();
            $this->lastAddedProduct = false;
            if ($productId) {
                try {
                    $this->lastAddedProduct = $this->productRepository->getById($productId);
                } catch (NoSuchEntityException $e) {
                    $this->lastAddedProduct = false;
                }
            }
        }

        return $this->lastAddedProduct;
    }

    /**
     * Retrieve the product Id of the last product added to cart
     *
     * @return int|false
     */
    private function getLastAddedProductId()
    {
        return $this->checkoutSession->getLastAddedProductId();
    }
}
