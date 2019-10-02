<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Model\Category;

use Smile\ElasticsuiteMerchandisingGauge\Model\MeasureInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory as ProductCollectionFactory;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteMerchandisingGauge\Search\Request\Product\Aggregation\Provider\MerchandisingMetricsProvider;
use Smile\ElasticsuiteBehavioralData\Model\Config as BehavioralDataConfig;
use Smile\ElasticsuiteCore\Search\Request\SortOrderInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class Measure
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class Measure implements MeasureInterface
{
    const PERCENTILE_0  = "0.0";
    const PERCENTILE_1  = "1.0";
    const PERCENTILE_5  = "5.0";
    const PERCENTILE_25 = "25.0";
    const PERCENTILE_50 = "50.0";
    const PERCENTILE_75 = "75.0";
    const PERCENTILE_95 = "95.0";
    const PERCENTILE_99 = "99.0";

    /**
     * @var CategoryInterface
     */
    private $category;

    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ContextInterface
     */
    private $searchContext;

    /**
     * @var BehavioralDataConfig
     */
    private $behavioralConfig;

    /**
     * @var MerchandisingMetricsProvider
     */
    private $metricsProvider;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var array
     */
    private $scoreCoefficients;

    /**
     * Measure constructor.
     *
     * @param CategoryInterface            $category          Category to measure.
     * @param ProductCollectionFactory     $collectionFactory Product collection factory.
     * @param QueryFactory                 $queryFactory      Query factory.
     * @param ContextInterface             $searchContext     Search context.
     * @param BehavioralDataConfig         $behavioralConfig  Behavioral data config.
     * @param MerchandisingMetricsProvider $metricsProvider   Merchandising metrics provider.
     * @param int                          $size              First products sample size to take into account.
     */
    public function __construct(
        CategoryInterface $category,
        ProductCollectionFactory $collectionFactory,
        QueryFactory $queryFactory,
        ContextInterface $searchContext,
        BehavioralDataConfig $behavioralConfig,
        MerchandisingMetricsProvider $metricsProvider,
        $size = 10
    ) {
        $this->category             = $category;
        $this->collectionFactory    = $collectionFactory;
        $this->queryFactory         = $queryFactory;
        $this->searchContext        = $searchContext;
        $this->behavioralConfig     = $behavioralConfig;
        $this->metricsProvider      = $metricsProvider;
        $this->size                 = ($size > 0) ? $size : 10;
        $this->scoreCoefficients    = [];
        $this->prepareScoreCoefficients();
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {

        $data = [
            'available_dimensions' => [],
            'dimension'     => null,
            'percentiles'   => [],
            'score'         => [
                'range'     => [],
                'current'   => null,
                'products'  => [],
            ],
        ];

        /*
         * (2) collection WITH blacklisted_products WITH aggregations, of size 0
         * => select the metric to use
         */
        $availableDimensions = $this->getDimensionsToUse();
        if (!empty($availableDimensions)) {
            $dimension = key($availableDimensions);
            $percentiles = $availableDimensions[$dimension]['values'];

            /*
             * (3) collection WITH blacklisted_products WITHOUT aggregations
             * => sorted by the metric DESC == BEST POSITIONS
             */
            $scoreRange = $this->getScoreRange($dimension);

            /*
             * (1) collection WITHOUT blacklisted_products WITHOUT aggregations
             * => extract individual metrics data
             */
            $productsScore = $this->getProductsScore($dimension, $percentiles);

            $max = max($productsScore['raw_values']);
            if ($max > 0) {
                foreach ($productsScore['raw_values'] as &$value) {
                    $value *= (100.0 / $max);
                }
            }

            $data = [
                'available_dimensions' => array_keys($availableDimensions),
                'dimension'     => $dimension,
                'percentiles'   => $percentiles,
                'score' => [
                    'range'     => $scoreRange,
                    'current'   => $productsScore['global'],
                    'products'  => $productsScore['products'],
                    'raw_values' => $productsScore['raw_values'],
                ],
            ];
        }

        return $data;
    }

    /**
     * Prepare the positions score coefficients.
     * It is a collection-size dependent array of coefficients depending on position :
     * [1, 1/2, 1/3, 1/4, 1/5, ...]
     *
     * @return void
     */
    private function prepareScoreCoefficients()
    {
        // TODO ribay@smile.fr: check on this-size really necessary ?
        if ($this->size > 0) {
            $this->scoreCoefficients = array_fill(0, $this->size, 1.0);
            array_walk($this->scoreCoefficients, [$this, 'getPositionCoefficient']);
        }
    }

    /**
     * Compute position-related coefficient.
     *
     * @param float $coefficient Current coefficient.
     * @param int   $position    Coefficient position.
     *
     * @return void
     */
    private function getPositionCoefficient(&$coefficient, $position)
    {
        $coefficient /= ($position + 1.0);
    }

    /**
     * Return the dimension to use and its data
     * TODO ribay@smile.fr : delegate to a "metrics evaluator" (that can be changed through the DI) ?
     *
     * @return array
     */
    private function getDimensionsToUse()
    {
        $usableDimensions = [];

        $collection = $this->getBaseProductCollection();
        $collection->setPageSize(0);

        /*
         * Iterate on all possible metrics to find one that can be used.
         */
        $facetedData = $collection->getFacetedData(MerchandisingMetricsProvider::BASE_AGGREGATION);
        $metrics = $facetedData[MerchandisingMetricsProvider::METRICS_CONTAINER] ?? [];
        if (!empty($metrics)) {
            if ($this->isUsableMetric($metrics[MerchandisingMetricsProvider::WEEKLY_SALES_EXTENDED_STATS] ?? [])) {
                $usableDimensions = [
                    MerchandisingMetricsProvider::WEEKLY_SALES => $metrics[MerchandisingMetricsProvider::WEEKLY_SALES_PERCENTILES],
                ];
            }

            if ($this->isUsableMetric($metrics[MerchandisingMetricsProvider::DAILY_SALES_EXTENDED_STATS] ?? [])) {
                $usableDimensions = [
                    MerchandisingMetricsProvider::DAILY_SALES => $metrics[MerchandisingMetricsProvider::DAILY_SALES_PERCENTILES],
                ];
            }

            if ($this->isUsableMetric($metrics[MerchandisingMetricsProvider::WEEKLY_VIEWS_EXTENDED_STATS] ?? [])) {
                $usableDimensions = [
                    MerchandisingMetricsProvider::WEEKLY_VIEWS => $metrics[MerchandisingMetricsProvider::DAILY_SALES_PERCENTILES],
                ];
            }

            if ($this->isUsableMetric($metrics[MerchandisingMetricsProvider::DAILY_VIEWS_EXTENDED_STATS] ?? [])) {
                $usableDimensions = [
                    MerchandisingMetricsProvider::DAILY_VIEWS => $metrics[MerchandisingMetricsProvider::DAILY_VIEWS_PERCENTILES],
                ];
            }

            if ($this->isUsableMetric($metrics[MerchandisingMetricsProvider::TOTAL_SALES_EXTENDED_STATS] ?? [])) {
                $usableDimensions = [
                    MerchandisingMetricsProvider::TOTAL_SALES => $metrics[MerchandisingMetricsProvider::TOTAL_SALES_PERCENTILES],
                ];
            }

            if ($this->isUsableMetric($metrics[MerchandisingMetricsProvider::TOTAL_VIEWS_EXTENDED_STATS] ?? [])) {
                $usableDimensions = [
                    MerchandisingMetricsProvider::TOTAL_VIEWS => $metrics[MerchandisingMetricsProvider::TOTAL_VIEWS_PERCENTILES],
                ];
            }
        }

        return $usableDimensions;
    }

    /**
     * Return if a given metric is usable according to its statistics.
     *
     * @param array $statistics Metric statistics
     *
     * @return bool
     */
    private function isUsableMetric($statistics)
    {
        $usable = false;

        if (!empty($statistics)) {
            $usable = ($statistics['count'] > 0) && ($statistics['avg'] > 0);
        }

        return $usable;
    }

    /**
     * Get product dimension.
     *
     * @param ProductInterface $product   Product.
     * @param string           $dimension Dimension to get.
     *
     * @return mixed
     */
    private function getProductDimension($product, $dimension)
    {
        $dimensionPath    = str_replace('.', '/', $dimension);
        $productDimension = $product->getData('document_source/' . $dimensionPath);

        return $productDimension ?? 0;
    }

    /**
     * Compute the scores related to the best and worst positioning according to a given dimension.
     * - best score is best products at the best locations
     * - worst score is the worst products at the worst locations
     *
     * @param string $dimension Dimension
     *
     * @return array
     */
    private function getScoreRange($dimension)
    {
        $bestScore  = $this->getBestScore($dimension);
        $worstScore = $this->getWorstScore($dimension);

        return [$worstScore, $bestScore];
    }

    /**
     * Return the score associated with the best positioning of products according to a given dimension.
     * That is: the products with the best individual score at the best positions.
     *
     * @param string $dimension Dimension
     *
     * @return float
     */
    private function getBestScore($dimension)
    {
        $score = 0.0;

        $dimensionField = $this->metricsProvider->getDimensionRelatedField($dimension);
        $dimensionFieldPath = $this->metricsProvider->getDimensionRelatedFieldPath($dimension);

        $collection = $this->getBaseProductCollection();
        $collection->setPageSize($this->size);
        $collection->setOrder($dimensionField, SortOrderInterface::SORT_DESC);

        $position = 0;
        foreach ($collection as $product) {
            if (!isset($this->scoreCoefficients[$position])) {
                break;
            }
            $productDimension = $product->getData($dimensionFieldPath) ?? 0;
            $score += $this->scoreCoefficients[$position] * $productDimension;
            $position++;
        }

        return $score;
    }

    /**
     * Return the score associated with the best positioning of products according to a given dimension.
     * That is: the products with the worst individual score at the best positions.
     *
     * @param string $dimension Dimension
     *
     * @return float
     */
    private function getWorstScore($dimension)
    {
        $score = 0.0;

        $dimensionField = $this->metricsProvider->getDimensionRelatedField($dimension);
        $dimensionFieldPath = $this->metricsProvider->getDimensionRelatedFieldPath($dimension);

        $collection = $this->getBaseProductCollection();
        $collection->setPageSize($this->size);
        $collection->setOrder($dimensionField, SortOrderInterface::SORT_ASC);

        $position = 0;
        foreach ($collection as $product) {
            if (!isset($this->scoreCoefficients[$position])) {
                break;
            }
            $productDimension = $product->getData($dimensionFieldPath) ?? 0;
            $score += $this->scoreCoefficients[$position] * $productDimension;
            $position++;
        }

        return $score;
    }

    /**
     * According to a given dimension and percentiles Extract individual products dimension and determine both
     * - the global score
     * - which percentile each product belongs to
     *
     * @param string $dimension   Dimension.
     * @param array  $percentiles Dimension percentiles.
     *
     * @return array
     */
    private function getProductsScore($dimension, $percentiles)
    {
        $globalScore = 0;
        $productsPercentiles = [];
        $rawValues = [];
        $dimensionFieldPath = $this->metricsProvider->getDimensionRelatedFieldPath($dimension);

        $products = $this->getCurrentProducts();
        $position = 0;
        foreach ($products as $product) {
            if (!isset($this->scoreCoefficients[$position])) {
                break;
            }
            $productDimension = $product->getData($dimensionFieldPath) ?? 0;
            $globalScore += $this->scoreCoefficients[$position] * $productDimension;
            $productsPercentiles[$product->getId()] = $this->getPercentile($productDimension, $percentiles);
            $rawValues[$product->getId()] = $productDimension;
            $position++;
        }

        return ['global' => $globalScore, 'products' => $productsPercentiles, 'raw_values' => $rawValues];
    }

    /**
     * Return products visible in the preview, taking into account
     * - the blacklisted products
     * - the manually sorted products
     *
     * @return array
     */
    private function getCurrentProducts()
    {
        $sortedProducts = $this->getSortedProducts();

        // Collection without neither the blacklisted products nor the manually sorted products.
        $collection = $this->removeBlacklistedProducts($this->getBaseProductCollection());
        $collection = $this->removeSortedProducts($collection);
        $collection->setPageSize($this->size);

        $products = $collection->getItems();

        $products = $sortedProducts + $products;
        $products = array_slice($products, 0, $this->size, true);

        return $products;
    }

    /**
     * Get the percentile a given value belongs to
     *
     * @param float $value       Value to get percentile for.
     * @param array $percentiles Values percentiles.
     *
     * @return string
     */
    private function getPercentile($value, $percentiles)
    {
        $percentile = self::PERCENTILE_0;

        if ($percentiles[self::PERCENTILE_1] !== "Nan") {
            $keys = [
                self::PERCENTILE_1,
                self::PERCENTILE_5,
                self::PERCENTILE_25,
                self::PERCENTILE_50,
                self::PERCENTILE_75,
                self::PERCENTILE_95,
                self::PERCENTILE_99,
            ];

            foreach ($keys as $step) {
                if ($value < $percentiles[$step]) {
                    break;
                }
                $percentile = $step;
            }
        }

        return $percentile;
    }

    /**
     * Return base product collection.
     * Used for determining best metric, which will be used to determine optimal positions.
     * This includes the blacklisted products.
     *
     * @return Collection
     */
    private function getBaseProductCollection()
    {
        $collection = $this->collectionFactory->create();

        $collection->setStoreId($this->category->getStoreId())
            ->addAttributeToSelect(['name']);

        return $this->prepareBaseProductCollection($collection);
    }

    /**
     * Prepare the product collection.
     *
     * @param Collection $collection Fulltext product collection
     *
     * @return Collection
     */
    private function prepareBaseProductCollection(Collection $collection)
    {
        $this->searchContext->setCurrentCategory($this->category);
        $this->searchContext->setStoreId($this->category->getStoreId());
        $collection->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]);

        $queryFilter = $this->getBaseQueryFilter();
        if ($queryFilter !== null) {
            $collection->addQueryFilter($queryFilter);
        }

        return $collection;
    }

    /**
     * Return the filter to apply to the query.
     *
     * @return QueryInterface
     */
    private function getBaseQueryFilter()
    {
        $query = null;

        $this->category->setIsActive(true);

        if ($this->category->getIsVirtualCategory() || $this->category->getId()) {
            $query = $this->category->getVirtualRule()->getCategorySearchQuery($this->category);
        }

        if ((bool) $this->category->getIsVirtualCategory() === false) {
            $queryParams = [];

            if ($query !== null) {
                $queryParams['should'][] = $query;
            }

            $idFilters = [
                'should'  => $this->category->getAddedProductIds(),
                'mustNot' => $this->category->getDeletedProductIds(),
            ];

            foreach ($idFilters as $clause => $productIds) {
                if ($productIds && !empty($productIds)) {
                    $queryParams[$clause][] = $this->getEntityIdFilterQuery($productIds);
                }
            }

            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryParams);
        }

        return $query;
    }

    /**
     * Return the sorted and non-blacklisted products as sorted.
     * TODO ribay@smile.fr : manually order the products
     *
     * @return ProductInterface[]
     */
    private function getSortedProducts()
    {
        $sortedProducts = [];

        $blacklistedProductIds = $this->category->getBlacklistedProductIds() ?? [];
        $sortedProductIds = $this->category->getSortedProductIds() ?? [];
        $sortedProductIds = array_diff($sortedProductIds, $blacklistedProductIds);
        if (!empty($sortedProductIds)) {
            $collection = $this->collectionFactory->create();
            $collection->setStoreId($this->category->getStoreId())
                ->addAttributeToSelect(['name']);

            // TODO ribay@smile.fr : centralize this ? part of prepareBaseProductCollection
            $this->searchContext->setCurrentCategory($this->category);
            $this->searchContext->setStoreId($this->category->getStoreId());
            $collection->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]);

            $queryParams = [];
            $queryParams['must'][] = $this->getEntityIdFilterQuery(array_values($sortedProductIds));
            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryParams);
            $collection->addQueryFilter($query);

            $sortedProducts = $collection->getItems();

            // Enforce local sort order.
            $sortedProducts = array_replace(array_flip($sortedProductIds), $sortedProducts);
        }

        return $sortedProducts;
    }

    /**
     * Remove blacklisted products from the collection
     *
     * @param Collection $collection Products collection
     *
     * @return Collection
     */
    private function removeBlacklistedProducts($collection)
    {
        $blacklistedProducts = $this->category->getBlacklistedProductIds() ?? [];
        if (!empty($blacklistedProducts)) {
            $queryParams = [];
            $queryParams['mustNot'][] = $this->getEntityIdFilterQuery($blacklistedProducts);

            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryParams);

            $collection->addQueryFilter($query);
        }

        return $collection;
    }

    /**
     * Remove sorted products from the collection
     *
     * @param Collection $collection Products collection.
     *
     * @return Collection
     */
    private function removeSortedProducts($collection)
    {
        $sortedProductIds = $this->category->getSortedProductIds() ?? [];
        if (!empty($sortedProductIds)) {
            $queryParams = [];
            $queryParams['mustNot'][] = $this->getEntityIdFilterQuery(array_values($sortedProductIds));

            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryParams);
            $collection->addQueryFilter($query);
        }

        return $collection;
    }

    /**
     * Create a product id filter query.
     *
     * @param array $ids Id to be filtered.
     *
     * @return QueryInterface
     */
    private function getEntityIdFilterQuery($ids) : QueryInterface
    {
        return $this->queryFactory->create(QueryInterface::TYPE_TERMS, ['field' => 'entity_id', 'values' => $ids]);
    }
}
