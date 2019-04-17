<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteRecommender\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;

/**
 * Generic recommender block.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class RecommenderList extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface[]
     */
    private $items;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher
     */
    private $model;

    /**
     * @var \Smile\ElasticsuiteRecommender\Helper\Data
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context               $context Block context.
     * @param \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model   Recommender model.
     * @param \Smile\ElasticsuiteRecommender\Helper\Data           $helper  Data helper.
     * @param array                                                $data    Additional block data.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model,
        \Smile\ElasticsuiteRecommender\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
            if ($product = $this->getProduct()) {
                $items = $this->model->getItems($product, $this->getBehavior(), $this->getPositionLimit());
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
        return $this->helper->getPositionLimit($this->getType());
    }

    /**
     * Recommendations loading behavior.
     *
     * @return int
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
