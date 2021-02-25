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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

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
    protected $catalogConfig;


    /**
     * @var CartProductProvider
     */
    protected $cartProductProvider;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig       Catalog configuration.
     * @param CartProductProvider           $cartProductProvider Cart products provider.
     */
    public function __construct(
        \Magento\Catalog\Model\Config $catalogConfig,
        CartProductProvider $cartProductProvider
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->cartProductProvider = $cartProductProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(ProductInterface $product, $size)
    {
        $collection = $this->createCollection($product)->setPageSize($size);
        $cartProductIds = $this->cartProductProvider->getCartProductIds();
        if (!empty($cartProductIds)) {
            $collection->addExcludeProductFilter($cartProductIds);
        }
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

    /**
     * Return the product collection made out of manual recommendations for a given product.
     *
     * @param ProductInterface $product Product to get manual recommendations for.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    abstract protected function createCollection(ProductInterface $product);
}
