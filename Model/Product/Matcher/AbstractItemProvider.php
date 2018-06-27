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

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ItemProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Load a product collection with manual recommendations.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
abstract class AbstractItemProvider implements ItemProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig Catalog configuration.
     */
    public function __construct(\Magento\Catalog\Model\Config $catalogConfig)
    {
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(ProductInterface $product)
    {
        $collection = $this->createCollection($product);
        $attributes = $this->catalogConfig->getProductAttributes();

        $collection->setPositionOrder()
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($attributes)
            ->addUrlRewrite();

        return $collection->getItems();
    }

    abstract protected function createCollection(ProductInterface $product);
}
