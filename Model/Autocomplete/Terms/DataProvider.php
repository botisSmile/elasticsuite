<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Terms;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Search\ResponseInterface;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Smile\ElasticsuiteCore\Helper\Autocomplete as ConfigurationHelper;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\Builder as RequestBuilder;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Instant Search popular queries data provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends \Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider implements DataProviderInterface
{
    /**
     * @var \Magento\Search\Model\Autocomplete\Item[]|null
     */
    private $items;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $type;

    /**
     * Constructor.
     *
     * @param QueryFactory          $queryFactory        Search query text factory.
     * @param ItemFactory           $itemFactory         Suggest terms item facory.
     * @param RequestBuilder        $requestBuilder      Search Request Builder.
     * @param SearchEngineInterface $searchEngine        Search Engine Interface.
     * @param ConfigurationHelper   $configurationHelper Autocomplete configuration helper.
     * @param RequestInterface      $request             Request Interface
     * @param string                $type                Autocomplete items type.
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ConfigurationHelper $configurationHelper,
        RequestBuilder $requestBuilder,
        SearchEngineInterface $searchEngine,
        RequestInterface $request,
        $type = self::AUTOCOMPLETE_TYPE
    ) {
        $this->requestBuilder      = $requestBuilder;
        $this->searchEngine        = $searchEngine;
        $this->itemFactory         = $itemFactory;
        $this->configurationHelper = $configurationHelper;
        $this->request             = $request;
        $this->type                = $type;

        parent::__construct($queryFactory, $itemFactory, $configurationHelper, $type);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if ($this->items === null) {
            $this->items = [];

            try {
                $collection = $this->getSearchTerms($this->getRawQueryText(), $this->getResultsPageSize());
                if ($this->configurationHelper->isEnabled($this->getType())) {
                    foreach ($collection as $item) {
                        $resultItem = $this->itemFactory->create([
                            'title'       => $item->getQueryText(),
                            'num_results' => $item->getNumResults(),
                            'type'        => $this->getType(),
                        ]);
                        $this->items[] = $resultItem;
                    }
                }
            } catch (\Exception $exception) {
                $this->items = [];
            }
        }

        if (empty($this->items)) {
            $this->items = parent::getItems();
        }

        return $this->items;
    }

    /**
     * Get search terms matching current query.
     *
     * @param string   $queryText The Query Text
     * @param int|null $maxSize   Number of search terms to retrieve
     *
     * @return \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSearchTerms($queryText, $maxSize = null)
    {
        $request = $this->getRequest($queryText, $maxSize);
        $result  = $this->searchEngine->search($request);

        $queries = $this->buildQueries($result);

        return $queries;
    }

    /**
     * Retrieve number of products to display in autocomplete results
     *
     * @return int
     */
    private function getResultsPageSize()
    {
        return $this->configurationHelper->getMaxSize($this->getType());
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

        return $this->requestBuilder->create(
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
            'matchPhrasePrefixQuery',
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
