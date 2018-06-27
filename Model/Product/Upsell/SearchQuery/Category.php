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
namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Coocurence;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\Nested;
use Magento\Catalog\Api\Data\ProductInterface;

class Category implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var Coocurence
     */
    private $coocurence;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory
     * @param Coocurence   $coocurence
     */
    public function __construct(QueryFactory $queryFactory, Coocurence $coocurence)
    {
        $this->queryFactory = $queryFactory;
        $this->coocurence   = $coocurence;
    }

    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        if ($categoryIds = $this->getCategories($product)) {
            $categoryQuery = $this->queryFactory->create(QueryInterface::TYPE_TERMS, ['field' => 'category.category_id', 'values' => $categoryIds]);

            $query = $this->queryFactory->create(QueryInterface::TYPE_NESTED,
                ['scoreMode' => Nested::SCORE_MODE_SUM, 'path' => 'category', 'query' => $categoryQuery]
            );
        }

        return $query;
    }

    private function getCategories(ProductInterface $product)
    {
        $categoryIds = $this->coocurence->getCoocurences('product_view', $product->getId(), $product->getStoreId(), 'category_view');

        if (empty($categoryIds)) {
            $categoryIds = $product->getCategoryIds();
        }

        return array_diff($categoryIds, [$product->getStore()->getRootCategoryId()]);
    }
}