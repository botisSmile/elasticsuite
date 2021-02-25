<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Block\Product\Multiple;

use Magento\Catalog\Block\Product\AbstractProduct;

use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterfaceFactory;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider\ContextInterface as ProductProviderContextInterface;

/**
 * Generic multiple products recommender
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class RecommenderList extends AbstractProduct
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface[]
     */
    private $items;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterface
     */
    private $productProvider;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterfaceFactory
     */
    private $productProviderFactory;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider\ContextInterface
     */
    private $providerContext;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\Multiple
     */
    private $model;

    /**
     * @var \Smile\ElasticsuiteRecommender\Helper\Data
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context                        $context                Context.
     * @param ProductProviderInterfaceFactory                               $productProviderFactory Product provider factory.
     * @param ProductProviderContextInterface                               $providerContext        Product provider context.
     * @param \Smile\ElasticsuiteRecommender\Model\Product\Matcher\Multiple $model                  Recommender model.
     * @param \Smile\ElasticsuiteRecommender\Helper\Data                    $helper                 Data helper.
     * @param array                                                         $data                   Data.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        ProductProviderInterfaceFactory $productProviderFactory,
        ProductProviderContextInterface $providerContext,
        \Smile\ElasticsuiteRecommender\Model\Product\Matcher\Multiple $model,
        \Smile\ElasticsuiteRecommender\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productProviderFactory = $productProviderFactory;
        $this->providerContext = $providerContext;
        $this->model = $model;
        $this->helper = $helper;
    }

    /**
     * Indicates if the block have recommended items.
     *
     * @return boolean
     */
    public function hasItems()
    {
        return count($this->getAllItems()) > 0;
    }

    /**
     * Get recommended items.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getAllItems()
    {
        if ($this->items === null) {
            $items = [];

            $this->productProvider = $this->productProviderFactory->create(['context' => $this->providerContext]);
            $sourceProducts = $this->productProvider->getProducts();
            if (!empty($sourceProducts)) {
                $items = $this->model->getItems($sourceProducts, $this->getPositionLimit());
            }

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * Are the result randomized.
     *
     * @return boolean
     */
    public function isShuffled()
    {
        return false;
    }

    /**
     * Number of recommendations to load.
     *
     * @return number
     */
    public function getPositionLimit()
    {
        return $this->providerContext->getMaxSize();
    }

    /**
     * Recommendations loading behavior.
     *
     * @return int
     * @deprecated
     */
    public function getBehavior()
    {
        return $this->helper->getBehavior($this->getType());
    }

    /**
     * Indicate if items can be added to the cart.
     *
     * @return boolean
     */
    public function canItemsAddToCart()
    {
        foreach ($this->getAllItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }

        return false;
    }
}
