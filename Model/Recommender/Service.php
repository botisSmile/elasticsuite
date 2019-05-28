<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteFacetRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteFacetRecommender\Model\Recommender;

use Magento\Framework\Search\ResponseInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteFacetRecommender\Api\FacetRecommenderServiceInterface;

/**
 * ElasticSuite Smart facets service implementation
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Service implements FacetRecommenderServiceInterface
{
    /**
     * @var \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterfaceFactory
     */
    private $recommendationFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Builder
     */
    private $searchRequestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    private $searchEngine;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory
     */
    private $aggregationFactory;

    public function __construct(
        \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterfaceFactory $recommendationFactory,
        \Smile\ElasticsuiteCore\Search\Request\Builder $searchRequestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory $aggregationFactory
    ) {
        $this->recommendationFactory = $recommendationFactory;
        $this->searchRequestBuilder  = $searchRequestBuilder;
        $this->searchEngine          = $searchEngine;
        $this->queryFactory          = $queryFactory;
        $this->aggregationFactory    = $aggregationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFacetsRecommendations($vid, $uid, $categoryId)
    {
        $request = $this->getRequest($uid, $categoryId);
        $result  = $this->searchEngine->search($request);

        $recommendations = $this->buildRecommendations($result);

        return $recommendations;
    }

    /**
     * @param \Magento\Framework\Search\ResponseInterface $response Search Response
     *
     * @return \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterface[]
     */
    private function buildRecommendations(ResponseInterface $response)
    {
        $recommendations = [];

        $filterBucket = $response->getAggregations()->getBucket('filter_name');
        if ($filterBucket) {
            /** @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\Aggregation\Value $childBucket */
            foreach ($filterBucket->getValues() as $childBucket) {
                $filterName = $childBucket->getValue();
                if ($childBucket->getAggregations()) {
                    foreach ($childBucket->getAggregations() as $subAggregation) {
                        foreach ($subAggregation->getValues() as $aggValue) {
                            if ($aggValue->getValue() !== '__other_docs') {
                                $recommendations[] = $this->recommendationFactory->create(
                                    [
                                        'data' => ['name' => $filterName, 'value' => $aggValue->getValue()],
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get request.
     *
     * @param string $uid        The user Uid (long duration identifier)
     * @param int    $categoryId The current category Id
     *
     * @return \Smile\ElasticsuiteCore\Search\RequestInterface
     */
    private function getRequest($uid, $categoryId)
    {
        $storeId      = $this->getStoreId();
        $aggregations = $this->getAggregations();
        $searchQuery  = $this->getSearchQuery($uid, $categoryId);

        return $this->searchRequestBuilder->create(
            $storeId,
            'tracking_log_event',
            0,
            0,
            $searchQuery,
            [],
            [],
            [],
            $aggregations
        );
    }

    private function getStoreId()
    {
        return 1;
    }

    /**
     * Get search query
     *
     * @param string $uid        The user Uid (long duration identifier)
     * @param int    $categoryId The current category Id
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getSearchQuery($uid, $categoryId)
    {
        $uidFilter      = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'session.uid', 'value' => $uid]
        );
        $pageFilter     = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'page.type.identifier', 'value' => 'catalog_category_view']
        );
        $categoryFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'page.category.id', 'value' => $categoryId]
        );

        return $this->queryFactory->create(QueryInterface::TYPE_BOOL, ['must' => [$uidFilter, $pageFilter, $categoryFilter]]);
    }

    /**
     * Get aggregations
     *
     * @return BucketInterface[]
     */
    private function getAggregations()
    {
        $filterValueAgg = $this->aggregationFactory->create(
            BucketInterface::TYPE_TERM,
            [
                'field' => 'page.product_list.filters.value',
                'name'  => 'filter_value',
                'size'  => 10,
            ]
        );

        $filterNameAgg = $this->aggregationFactory->create(
            BucketInterface::TYPE_TERM,
            [
                'field'        => 'page.product_list.filters.name',
                'name'         => 'filter_name',
                'nestedPath'   => 'page.product_list.filters',
                'childBuckets' => [$filterValueAgg],
            ]
        );

        return [$filterNameAgg];
    }
}
