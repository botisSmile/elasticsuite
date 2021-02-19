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

namespace Smile\ElasticsuiteRecommender\Model\Product\CrossSell;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\AbstractItemProvider;

/**
 * Load a product collection with manual cross-sell products recommendations.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class ItemProvider extends AbstractItemProvider
{
    /**
     * {@inheritDoc}
     */
    protected function createCollection(ProductInterface $product)
    {
        return $product->getCrossSellProductCollection();
    }
}
