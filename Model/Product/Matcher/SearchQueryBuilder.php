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
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;

/**
 * Recommender search query builder.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class SearchQueryBuilder implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var SearchQueryBuilderInterface[]
     */
    private $searchQueries;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory  Search query factory.
     * @param array        $searchQueries Query clause builder.
     */
    public function __construct(QueryFactory $queryFactory, array $searchQueries = [])
    {
        $this->queryFactory  = $queryFactory;
        $this->searchQueries = $searchQueries;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;
        $queryClauses = [];

        foreach ($this->searchQueries as $clause => $queries) {
            $queryClauses[$clause] = [];
            foreach ($queries as $subQuery) {
                if ($queryPart = $subQuery->getSearchQuery($product)) {
                    if (!is_array($queryPart)) {
                        $queryPart = [$queryPart];
                    }
                    $queryClauses[$clause] += $queryPart;
                }
            }
        }

        $queryClauses = array_filter($queryClauses);

        if (!empty($queryClauses)) {
            $queryClauses['minimum_should_match'] = 1;
            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryClauses);
        }

        return $query;
    }
}
