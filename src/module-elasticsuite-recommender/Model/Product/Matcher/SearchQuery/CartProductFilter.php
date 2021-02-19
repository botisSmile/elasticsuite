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
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\CartProductProvider;

/**
 * Generic search query products in cart exclusion filter builder
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class CartProductFilter implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var CartProductProvider
     */
    private $cartProductProvider;

    /**
     * Constructor.
     *
     * @param QueryFactory        $queryFactory        Query factory.
     * @param CartProductProvider $cartProductProvider Cart products provider.
     */
    public function __construct(QueryFactory $queryFactory, CartProductProvider $cartProductProvider)
    {
        $this->queryFactory = $queryFactory;
        $this->cartProductProvider = $cartProductProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        $excludedProducts = $this->cartProductProvider->getCartProductIds();
        if (!empty($excludedProducts)) {
            $query = $this->queryFactory->create(QueryInterface::TYPE_TERMS, ['field' => 'entity_id', 'values' => $excludedProducts]);
        }

        return $query;
    }
}
