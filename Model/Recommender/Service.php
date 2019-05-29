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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\ResponseInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
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

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Service constructor.
     *
     * @param \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterfaceFactory $recommendationFactory Recommendation Factory
     * @param \Smile\ElasticsuiteCore\Search\Request\Builder                                   $searchRequestBuilder  Request Builder
     * @param \Magento\Search\Model\SearchEngine                                               $searchEngine          Search Engine
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory                        $queryFactory          Query Factory
     * @param \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory            $aggregationFactory    Aggregation Factory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                               $scopeConfig           Scope Configuration
     * @param \Magento\Store\Model\StoreManagerInterface                                       $storeManager          Store Manager
     */
    public function __construct(
        \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterfaceFactory $recommendationFactory,
        \Smile\ElasticsuiteCore\Search\Request\Builder $searchRequestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory $aggregationFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->recommendationFactory = $recommendationFactory;
        $this->searchRequestBuilder  = $searchRequestBuilder;
        $this->searchEngine          = $searchEngine;
        $this->queryFactory          = $queryFactory;
        $this->aggregationFactory    = $aggregationFactory;
        $this->scopeConfig           = $scopeConfig;
        $this->storeManager          = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFacetsRecommendations($vid, $uid, $categoryId)
    {
        $request = $this->getRequest($vid, $categoryId);
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
                            $metrics = $aggValue->getMetrics();
                            if ($aggValue->getValue() !== '__other_docs') {
                                $aggsData[] = [
                                    'name'  => $filterName,
                                    'value' => $aggValue->getValue(),
                                    'count' => $metrics['count'] ?? 0,
                                ];
                            }
                        }
                    }
                }
            }
        }

        usort($aggsData, function ($item1, $item2) {
            return $item2['count'] <=> $item1['count'];
        });

        foreach (array_slice($aggsData, 0, $this->getMaxSize()) as $recommendationData) {
            $recommendations[] = $this->recommendationFactory->create(['data' => $recommendationData]);
        }

        return $recommendations;
    }

    /**
     * Get request.
     *
     * @param string $vid        The user Vid (long duration identifier)
     * @param int    $categoryId The current category Id
     *
     * @return \Smile\ElasticsuiteCore\Search\RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRequest($vid, $categoryId)
    {
        $storeId      = $this->getStoreId();
        $aggregations = $this->getAggregations();
        $searchQuery  = $this->getSearchQuery($vid, $categoryId);

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

    /**
     * Get current Store Id.
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get max number of filters to be recommended.
     *
     * @return int
     */
    private function getMaxSize()
    {
        // @TODO get it from config.
        return 5;
    }

    /**
     * Get search query
     *
     * @param string $vid        The user Vid (long duration identifier)
     * @param int    $categoryId The current category Id
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getSearchQuery($vid, $categoryId)
    {
        $uidFilter      = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'session.vid', 'value' => $vid]
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
                'size'  => 5,
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
