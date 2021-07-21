<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Visitor;

use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Smile\ElasticsuiteCore\Search\Request\Builder as SearchRequestBuilder;
use Magento\Framework\Search\SearchEngineInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteTracker\Api\EventIndexInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteTracker\Helper\Data as TrackerHelper;

/**
 * Visitor product provider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class ProductProvider implements ProductProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchRequestBuilder
     */
    private $searchRequestBuilder;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var ContextInterface
     */
    private $productProviderContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TrackerHelper
     */
    private $trackerHelper;

    /**
     * @var array
     */
    private $aggregationProviders;

    /**
     * ContextProvider constructor.
     *
     * @param ProductRepositoryInterface   $productRepository      Product repository.
     * @param SearchRequestBuilder         $searchRequestBuilder   Search request builder.
     * @param SearchEngineInterface        $searchEngine           Search engine.
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory Criteria builder factory.
     * @param ContextInterface             $context                Product provider context.
     * @param StoreManagerInterface        $storeManager           Store manager.
     * @param TrackerHelper                $trackerHelper          Tracker Helper.
     * @param array                        $aggregationProviders   Aggregation Providers.
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchRequestBuilder $searchRequestBuilder,
        SearchEngineInterface $searchEngine,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        TrackerHelper $trackerHelper,
        array $aggregationProviders = []
    ) {
        $this->productRepository    = $productRepository;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->searchEngine         = $searchEngine;
        $this->criteriaBuilderFactory   = $criteriaBuilderFactory;
        $this->productProviderContext   = $context;
        $this->storeManager     = $storeManager;
        $this->trackerHelper    = $trackerHelper;
        $this->aggregationProviders = $aggregationProviders;
    }

    /**
     * Return the products to get recommendations for
     *
     * @param string $visitorId The visitor id, used when it's not fetchable from cookies.
     *
     * @return ProductInterface[]
     */
    public function getProducts($visitorId = null)
    {
        $products = [];

        $productIds = $this->getProductIds($visitorId);
        if (!empty($productIds)) {
            /** @var SearchCriteriaBuilder $criteriaBuilder */
            $criteriaBuilder = $this->criteriaBuilderFactory->create();
            $criteriaBuilder->addFilter('entity_id', $productIds, 'in');
            $criteria = $criteriaBuilder->create();

            $products = $this->productRepository->getList($criteria)->getItems();
        }

        return $products;
    }

    /**
     * Return the products ids to get recommendations for
     *
     * @param string $visitorId The visitor id, used when it's not fetchable from cookies.
     *
     * @return int[]
     */
    private function getProductIds($visitorId = null)
    {
        $maxSize = $this->productProviderContext->getMaxSize();
        $maxAge = $this->productProviderContext->getMaxAge();
        $categories = $this->productProviderContext->getCategories();

        $storeId = $this->storeManager->getStore()->getId();

        $productIds = [];
        try {
            $eventFilter = $this->getEventFilter(['catalog_product_view', 'checkout_onepage_success'], $maxAge, $visitorId);

            $aggregations  = $this->getAggregations($maxSize, $categories);

            $searchRequest  = $this->getSearchRequest($storeId, $eventFilter, $aggregations);
            $searchResponse = $this->searchEngine->search($searchRequest);

            foreach ($this->aggregationProviders as $aggregationProvider) {
                /** @var \Smile\ElasticsuiteRecommender\Model\Product\Visitor\AggregationProviderInterface $aggregationProvider */
                $aggName = $aggregationProvider->getAggregationName();
                foreach ($searchResponse->getAggregations()->getBucket($aggName)->getValues() as $value) {
                    if ($value->getValue() != "__other_docs") {
                        $productIds[] = (int) $value->getValue();
                    }
                }
            }
        } catch (\Exception $e) {
            ;
        }

        $productIds = array_slice(array_unique($productIds), 0, $maxSize);

        return $productIds;
    }

    /**
     * Build the search request used to collect past products of interest for the visitor.
     *
     * @param integer $storeId      Store id.
     * @param array   $eventFilter  Event filter.
     * @param string  $aggregations Aggregations.
     *
     * @return \Smile\ElasticsuiteCore\Search\RequestInterface
     */
    private function getSearchRequest($storeId, $eventFilter, $aggregations)
    {
        $index = EventIndexInterface::INDEX_IDENTIFIER;

        return $this->searchRequestBuilder->create($storeId, $index, 0, 0, null, [], [], $eventFilter, $aggregations);
    }

    /**
     * Filter used to match events.
     *
     * @param array   $pageTypes Page type identifiers
     * @param integer $maxAge    Event max age.
     * @param string  $visitorId The visitor id, used when it's not fetchable from cookies.
     *
     * @return array
     */
    private function getEventFilter($pageTypes = [], $maxAge = 0, $visitorId = null)
    {
        $filter = [];

        if (null === $visitorId) {
            $visitorId = $this->trackerHelper->getCurrentVisitorId();
        }

        if (null !== $visitorId) {
            $filter['session.vid'] = $visitorId;
        }

        if (!empty($pageTypes)) {
            $filter['page.type.identifier'] = $pageTypes;
        }

        if ($maxAge > 0) {
            $earlierDate = new \DateTime();
            $earlierDate->modify(sprintf('- %d days', $maxAge));
            $filter['date'] = ['gt' => $earlierDate->format('Y-m-d')];
        }

        return $filter;
    }

    /**
     * Build the aggregations used to collect products of interest
     *
     * @param integer $size       Aggregation size.
     * @param array   $categories Contextual categories.
     *
     * @return BucketInterface[]
     */
    private function getAggregations($size, $categories = [])
    {
        $aggs = [];

        foreach ($this->aggregationProviders as $aggregationProvider) {
            /** @var \Smile\ElasticsuiteRecommender\Model\Product\Visitor\AggregationProviderInterface $aggregationProvider */
            $aggs[] = $aggregationProvider->getAggregation($size, $categories);
        }

        return $aggs;
    }
}
