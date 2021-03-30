<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralAutocomplete
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBehavioralAutocomplete\Model;

use Magento\Framework\Search\ResponseInterface;
use Magento\Framework\Stdlib\StringUtils as StdlibString;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteBehavioralAutocomplete\Api\TrendingQueryServiceInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\FunctionScore;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Trending Query service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralAutocomplete
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class TrendingQueryService implements TrendingQueryServiceInterface
{
    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    private $searchQueryFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Builder
     */
    private $searchRequestBuilder;

    /**
     * @var \Magento\Framework\Search\SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $string;

    /**
     * Service constructor.
     *
     * @param \Magento\Search\Model\QueryFactory                        $searchQueryFactory   Search Query Factory
     * @param \Smile\ElasticsuiteCore\Search\Request\Builder            $searchRequestBuilder Request Builder
     * @param \Magento\Framework\Search\SearchEngineInterface           $searchEngine         Search Engine
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory         Query Factory
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager         Store Manager
     * @param \Magento\Framework\App\RequestInterface                   $request              Request Interface
     * @param StdlibString                                              $string               String Utils
     */
    public function __construct(
        \Magento\Search\Model\QueryFactory $searchQueryFactory,
        \Smile\ElasticsuiteCore\Search\Request\Builder $searchRequestBuilder,
        \Magento\Framework\Search\SearchEngineInterface $searchEngine,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        StdlibString $string
    ) {
        $this->searchQueryFactory   = $searchQueryFactory;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->searchEngine         = $searchEngine;
        $this->queryFactory         = $queryFactory;
        $this->request              = $request;
        $this->storeManager         = $storeManager;
        $this->string               = $string;
    }

    /**
     * {@inheritdoc}
     */
    public function get($queryText = null, $maxSize = null)
    {
        if (null === $queryText) {
            $queryText = $this->getRawQueryText();
        }

        $request = $this->getRequest($queryText, $maxSize);
        $result  = $this->searchEngine->search($request);

        $queries = $this->buildQueries($result);

        return $queries;
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    private function getRawQueryText()
    {
        $queryText = $this->request->getParam(QueryFactory::QUERY_VAR_NAME);

        return ($queryText === null || is_array($queryText)) ? '' : $this->string->cleanString(trim($queryText));
    }

    /**
     * @param \Magento\Framework\Search\ResponseInterface $response Search Response
     *
     * @return \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterface[]
     */
    private function buildQueries(ResponseInterface $response)
    {
        $queries = [];

        $searchBucket = $response->getAggregations()->getBucket('search_query');

        if ($searchBucket) {
            /** @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\Aggregation\Value $childBucket */
            foreach ($searchBucket->getValues() as $childBucket) {
                if ($childBucket->getValue() != '__other_docs') {
                    $metrics   = $childBucket->getMetrics();
                    $queries[] = $this->searchQueryFactory->create(
                        [
                            'data' => [
                                'query_text'  => $this->string->cleanString($childBucket->getValue()),
                                'num_results' => round($metrics['product_count'] ?? 0),
                            ],
                        ]
                    );
                }
            }
        }

        return $queries;
    }

    /**
     * Get request.
     *
     * @param string $queryText Current queryText to base search on.
     * @param int    $maxSize   Max size of queries to fetch.
     *
     * @return \Smile\ElasticsuiteCore\Search\RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRequest($queryText, $maxSize = null)
    {
        $storeId      = $this->getStoreId();
        $aggregations = $this->getAggregations($maxSize);
        $searchQuery  = $this->getSearchQuery($queryText);
        $boostedQuery = $this->getBoostedQuery($searchQuery);

        return $this->searchRequestBuilder->create(
            $storeId,
            'tracking_log_event',
            0,
            0,
            $boostedQuery,
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
     * Apply a boost on the search query.
     * Prioritize most recently used search terms.
     *
     * @param \Smile\ElasticsuiteCore\Search\Request\QueryInterface $searchQuery Search Query
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getBoostedQuery(QueryInterface $searchQuery)
    {
        try {
            $date      = new \DateTime();
            $functions = [
                [
                    'gauss' => [
                        'date' => [
                            'origin' => $date->format(\Magento\Framework\DB\Adapter\Pdo\Mysql::DATETIME_FORMAT),
                            'scale'  => '7d',
                            'offset' => 0,
                            'decay'  => 0.1,
                        ],
                    ],
                ],
            ];

            $queryParams = [
                'query'     => $searchQuery,
                'functions' => $functions,
                'scoreMode' => FunctionScore::SCORE_MODE_MULTIPLY,
                'boostMode' => FunctionScore::BOOST_MODE_MULTIPLY,
            ];

            return $this->queryFactory->create(QueryInterface::TYPE_FUNCTIONSCORE, $queryParams);
        } catch (\Exception $exception) {
            return $searchQuery;
        }
    }

    /**
     * Get search query
     *
     * @param string $queryText Current queryText to base search on.
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getSearchQuery($queryText)
    {
        $pageFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'page.type.identifier', 'value' => 'catalogsearch_result_index']
        );

        $spellcheckFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'page.search.is_spellchecked', 'value' => false]
        );

        $productCountFilter = $this->queryFactory->create(
            QueryInterface::TYPE_RANGE,
            ['field' => 'page.product_list.product_count', 'bounds' => ['gt' => 0]]
        );

        $noFilterFilter = $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            [
                'query' => $this->queryFactory->create(QueryInterface::TYPE_EXISTS, ['field' => 'page.product_list.filters']),
                'path'  => 'page.product_list.filters',
            ]
        );

        $matchFilter = $this->queryFactory->create(
            QueryInterface::TYPE_MATCHPHRASEPREFIX,
            ['field' => 'page.search.query', 'queryText' => $queryText]
        );

        return $this->queryFactory->create(
            QueryInterface::TYPE_BOOL,
            [
                'must'    => [$pageFilter, $spellcheckFilter, $productCountFilter, $matchFilter],
                'mustNot' => [$noFilterFilter],
            ]
        );
    }

    /**
     * Get aggregations
     *
     * @param int $maxSize The max size
     *
     * @return array
     */
    private function getAggregations($maxSize)
    {
        return [
            [
                'type'      => BucketInterface::TYPE_TERM,
                'field'     => 'page.search.query.sortable',
                'name'      => 'search_query',
                'size'      => (int) $maxSize,
                'sortOrder' => BucketInterface::SORT_ORDER_RELEVANCE,
                'metrics'   => [
                    [
                        'name'  => 'product_count',
                        'type'  => 'avg',
                        'field' => 'page.product_list.product_count',
                    ],
                ],
            ],
        ];
    }
}
