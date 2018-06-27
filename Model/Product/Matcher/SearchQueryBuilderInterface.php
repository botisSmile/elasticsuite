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

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Recommender search query builder.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
interface SearchQueryBuilderInterface
{
    /**
     * Build search query to match product recommendations.
     *
     * @param ProductInterface $product Product.
     *
     * @return QueryInterface
     */
    public function getSearchQuery(ProductInterface $product);
}
