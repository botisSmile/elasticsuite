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

namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\AbstractItemProvider;

/**
 * Load a product collection with manual upsell products recommendations.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class ItemProvider extends AbstractItemProvider
{
    /**
     * {@inheritDoc}
     */
    protected function createCollection(ProductInterface $product)
    {
        return $product->getUpSellProductCollection();
    }
}
