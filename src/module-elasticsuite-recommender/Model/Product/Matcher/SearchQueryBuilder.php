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
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Helper\Data as DataHelper;

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
     * @var DataHelper
     */
    private $helper;

    /**
     * @var SearchQueryBuilderInterface[]
     */
    private $searchQueries;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory  Search query factory.
     * @param DataHelper   $helper        Data helper.
     * @param array        $searchQueries Query clause builder.
     */
    public function __construct(QueryFactory $queryFactory, DataHelper $helper, array $searchQueries = [])
    {
        $this->queryFactory  = $queryFactory;
        $this->helper        = $helper;
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
                    $queryClauses[$clause][] = $queryPart;
                }
            }
        }

        $queryClauses = array_filter($queryClauses);

        if ($this->helper->isPreventingZeroConstraintsRequests()) {
            if (!isset($queryClauses['must']) && !isset($queryClauses['should'])) {
                // Without any constraint clause, the whole catalog is a candidate, so the recommendation lacks any intelligence.
                $queryClauses = [];
            }
        }

        if (!empty($queryClauses)) {
            $queryClauses['minimum_should_match'] = 1;
            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryClauses);
        }

        return $query;
    }
}
