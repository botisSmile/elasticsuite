<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider as CategoryFilterProvider;
use Smile\ElasticsuiteCore\Api\Client\ClientInterface;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponseFactory;
use Smile\ElasticsuiteCore\Search\Request\Builder;
use Smile\ElasticsuiteCore\Search\Request\SortOrderInterface;
use Smile\ElasticsuiteCore\Search\RequestInterface;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;

/**
 * Result Model for Explain
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Result
{
    /**
     * @var Result\ItemFactory
     */
    private $previewItemFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Request\Mapper
     */
    private $requestMapper;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponseFactory
     */
    private $queryResponseFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Search\ContextInterface
     */
    private $searchContext;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface
     */
    private $containerConfiguration;

    /**
     * @var \Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider
     */
    private $categoryFilterProvider;

    /**
     * @var \Smile\ElasticsuiteExplain\Model\Result\CollectorInterface[]
     */
    private $collectors;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    private $category;

    /**
     * @var null|string
     */
    private $queryText = null;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var boolean
     */
    private $isSpellchecked;

    /**
     * Constructor.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param \Smile\ElasticsuiteExplain\Model\Result\ItemFactory                $previewItemFactory     Preview item factory.
     * @param ContainerConfigurationInterface                                    $containerConfig        Container Configuration
     * @param \Smile\ElasticsuiteCore\Search\Request\Builder                     $requestBuilder         Request Builder
     * @param \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Request\Mapper $requestMapper          Request Mapper
     * @param \Smile\ElasticsuiteCore\Api\Client\ClientInterface                 $client                 Elasticsearch client
     * @param QueryResponseFactory                                               $queryResponseFactory   Query Response
     * @param \Smile\ElasticsuiteCore\Api\Search\ContextInterface                $searchContext          Search Context
     * @param \Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider          $categoryFilterProvider Category Filter Provider
     * @param \Smile\ElasticsuiteExplain\Model\Result\CollectorInterface[]       $collectors             Explain collectors
     * @param \Magento\Catalog\Api\Data\CategoryInterface|null                   $category               Category Id to preview, if any.
     * @param null                                                               $queryText              Query Text.
     * @param int                                                                $size                   Preview size.
     */
    public function __construct(
        Result\ItemFactory $previewItemFactory,
        ContainerConfigurationInterface $containerConfig,
        Builder $requestBuilder,
        \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Request\Mapper $requestMapper,
        \Smile\ElasticsuiteCore\Api\Client\ClientInterface $client,
        QueryResponseFactory $queryResponseFactory,
        ContextInterface $searchContext,
        CategoryFilterProvider $categoryFilterProvider,
        array $collectors = [],
        CategoryInterface $category = null,
        $queryText = null,
        $size = 10
    ) {
        $this->size                   = $size;
        $this->previewItemFactory     = $previewItemFactory;
        $this->requestBuilder         = $requestBuilder;
        $this->categoryFilterProvider = $categoryFilterProvider;
        $this->requestMapper          = $requestMapper;
        $this->client                 = $client;
        $this->queryResponseFactory   = $queryResponseFactory;
        $this->searchContext          = $searchContext;
        $this->collectors             = $collectors;
        $this->queryText              = $queryText;
        $this->containerConfiguration = $containerConfig;
        $this->category               = $category;
    }

    /**
     * Load preview data.
     *
     * @return array
     */
    public function getData()
    {
        $results  = $this->getResults();
        $products = $this->preparePreviewItems($results);

        $results = [
            'products'        => array_values($products),
            'size'            => $results->count(),
            'is_spellchecked' => $this->isSpellchecked,
        ];

        foreach ($this->collectors as $collector) {
            $results = array_merge_recursive($results, $collector->collect($this->searchContext, $this->containerConfiguration));
        }

        return $results;
    }

    /**
     * @return \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponse
     */
    private function getResults()
    {
        $request                   = $this->prepareRequest();
        $explainRequest            = $this->requestMapper->buildSearchRequest($request);
        $explainRequest['explain'] = true;
        $this->isSpellchecked      = $request->isSpellchecked();

        $explainResponse = $this->client->search(['index' => $request->getIndex(), 'body' => $explainRequest]);

        return $this->queryResponseFactory->create(['searchResponse' => $explainResponse]);
    }

    /**
     * Prepare the search request before it will be executed.
     *
     * @return RequestInterface
     */
    private function prepareRequest()
    {
        // Store id and request name.
        $storeId           = $this->containerConfiguration->getStoreId();
        $searchRequestName = $this->containerConfiguration->getName();

        // Pagination params.
        $size = $this->size;
        $from = 0;

        // Setup filters.
        $filters = [];

        // Setup sort orders.
        $sortOrders = [];

        // Setup query text.
        $queryText = null;

        if ($this->queryText !== null) {
            $queryText  = $this->queryText;
            $sortOrders = $this->getSearchQuerySortOrders();
        } elseif ($this->category !== null) {
            $filters[]  = $this->categoryFilterProvider->getQueryFilter($this->category);
            $sortOrders = $this->getCategorySortOrders($this->category);
        }

        return $this->requestBuilder->create(
            $storeId,
            $searchRequestName,
            $from,
            $size,
            $queryText,
            $sortOrders,
            $filters,
            [],
            []
        );
    }

    /**
     * Retrieve Category Sort Orders
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category The Category
     *
     * @return array
     */
    private function getCategorySortOrders(CategoryInterface $category)
    {
        return [
            'category.position' => [
                'direction'    => 'asc',
                'sortField'    => 'category.position',
                'nestedPath'   => 'category',
                'nestedFilter' => ['category.category_id' => $category->getId()],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getSearchQuerySortOrders()
    {
        $sortOrders = [];

        if ($this->searchContext->getCurrentSearchQuery()) {
            $searchQuery = $this->searchContext->getCurrentSearchQuery();
            if ($searchQuery->getId()) {
                $sortOrders['search_query.position'] = [
                    'direction'    => SortOrderInterface::SORT_ASC,
                    'nestedFilter' => ['search_query.query_id' => $searchQuery->getId()],
                ];
            }
        }

        return $sortOrders;
    }

    /**
     * Convert an array of products to an array of preview items.
     *
     * @param \Magento\Framework\Search\ResponseInterface $queryResponse The Query response, with products as documents.
     *
     * @return Result\Item[]
     */
    private function preparePreviewItems(\Magento\Framework\Search\ResponseInterface $queryResponse)
    {
        $items = [];

        /** @var \Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response\ExplainDocument $document */
        foreach ($queryResponse->getIterator() as $document) {
            $item                      = $this->previewItemFactory->create(['document' => $document]);
            $items[$document->getId()] = $item->getData();
        }

        return $items;
    }
}
