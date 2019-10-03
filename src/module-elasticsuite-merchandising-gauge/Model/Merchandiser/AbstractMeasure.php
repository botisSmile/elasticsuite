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

namespace Smile\ElasticsuiteMerchandisingGauge\Model\Merchandiser;

use Smile\ElasticsuiteMerchandisingGauge\Model\MeasureInterface;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory as ProductCollectionFactory;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteMerchandisingGauge\Model\DimensionProvider;
use Smile\ElasticsuiteMerchandisingGauge\Model\Dimension;
use Smile\ElasticsuiteMerchandisingGauge\Search\Request\Product\Aggregation\Provider\MerchandisingMetricsProvider;
use Smile\ElasticsuiteCore\Search\Request\SortOrderInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class AbstractMeasure
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
abstract class AbstractMeasure implements MeasureInterface
{
    /**
     * @var ProductCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var ContextInterface
     */
    protected $searchContext;

    /**
     * @var DimensionProvider
     */
    private $dimensionProvider;

    /**
     * @var string
     */
    private $preferredDimension;

    /**
     * @var integer
     */
    private $sampleSize;

    /**
     * @var integer
     */
    private $pageSize;

    /**
     * @var array
     */
    protected $visibility;

    /**
     * @var array
     */
    private $scoreCoefficients;

    /**
     * Measure constructor.
     *
     * @param ProductCollectionFactory $collectionFactory  Product collection factory.
     * @param QueryFactory             $queryFactory       Query factory.
     * @param ContextInterface         $searchContext      Search context.
     * @param DimensionProvider        $dimensionProvider  Merchandising dimension provider.
     * @param string                   $preferredDimension Preferred dimension identifier.
     * @param int                      $sampleSize         First products sample size to take into account.
     * @param int                      $pageSize           Full preview size.
     * @param array                    $visibility         Products visibility.
     */
    public function __construct(
        ProductCollectionFactory $collectionFactory,
        QueryFactory $queryFactory,
        ContextInterface $searchContext,
        DimensionProvider $dimensionProvider,
        $preferredDimension,
        $sampleSize = 10,
        $pageSize = 10,
        $visibility = [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]
    ) {
        $this->collectionFactory    = $collectionFactory;
        $this->queryFactory         = $queryFactory;
        $this->searchContext        = $searchContext;
        $this->dimensionProvider    = $dimensionProvider;
        $this->preferredDimension   = $preferredDimension;
        $this->sampleSize           = ($sampleSize > 0) ? $sampleSize : 10;
        $this->pageSize             = ($pageSize > 0) ? $pageSize : 10;
        $this->visibility           = $visibility;
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
            'score'         => [
                'range'     => [],
                'current'   => null,
                'products'  => [],
            ],
        ];

        // Determine the usable metrics/dimensions.
        $availableDimensions = $this->getDimensionsToUse();
        if (!empty($availableDimensions)) {
            $dimensionsData = [];
            foreach ($availableDimensions as $dimension) {
                $dimensionsData[$dimension->getIdentifier()] = [
                    'identifier' => $dimension->getIdentifier(),
                    'label'      => __($dimension->getLabel()),
                    'valueLabelPattern' => __($dimension->getValueLabelPattern()),
                ];
            }

            $dimension = current($availableDimensions);
            if ($this->preferredDimension) {
                if (in_array($this->preferredDimension, array_keys($availableDimensions))) {
                    $dimension = $availableDimensions[$this->preferredDimension];
                }
            }

            // Determine the best and worst score possible according to the dimension.
            $scoreRange = $this->getScoreRange($dimension);

            // Compute the current products positions score and extract products dimension values.
            $productsScore = $this->getProductsScore($dimension);

            $data = [
                'available_dimensions'  => $dimensionsData,
                'dimension'             => $dimension->getIdentifier(),
                'score' => [
                    'range'     => $scoreRange,
                    'current'   => $productsScore['global'],
                    'products'  => $productsScore['products'],
                ],
            ];
        }

        return $data;
    }

    /**
     * Prepare the product collection.
     *
     * @param Collection $collection Fulltext product collection
     *
     * @return Collection
     */
    abstract protected function prepareBaseProductCollection(Collection $collection);

    /**
     * Return the list of blacklisted product ids.
     *
     * @return array
     */
    abstract protected function getBlacklistedProductIds();

    /**
     * Return the list of manually sorted product ids.
     *
     * @return array
     */
    abstract protected function getSortedProductIds();

    /**
     * Create a product id filter query.
     *
     * @param array $ids Id to be filtered.
     *
     * @return QueryInterface
     */
    protected function getEntityIdFilterQuery($ids) : QueryInterface
    {
        return $this->queryFactory->create(QueryInterface::TYPE_TERMS, ['field' => 'entity_id', 'values' => $ids]);
    }

    /**
     * Prepare the positions score coefficients.
     * It is a collection sample size dependent array of coefficients depending on position :
     * [1, 1/2, 1/3, 1/4, 1/5, ...]
     *
     * @return void
     */
    private function prepareScoreCoefficients()
    {
        $this->scoreCoefficients = array_fill(0, $this->sampleSize, 1.0);
        array_walk(
            $this->scoreCoefficients,
            function (&$coefficient, $position) {
                $coefficient /= ($position + 1.0);
            }
        );
    }

    /**
     * Return the dimensions that can be exploited.
     *
     * @return Dimension[]
     */
    private function getDimensionsToUse()
    {
        $collection = $this->getBaseProductCollection();
        $collection->setPageSize(0);

        // Iterate on all possible metrics to find one that can be used.
        $facetedData = $collection->getFacetedData(MerchandisingMetricsProvider::BASE_AGGREGATION);
        $metrics = $facetedData[MerchandisingMetricsProvider::METRICS_CONTAINER] ?? [];
        $usableDimensions = $this->dimensionProvider->getUsableDimensions($metrics);

        return $usableDimensions;
    }

    /**
     * Compute the scores related to the best and worst positioning according to a given dimension.
     * - best score is best products at the best locations
     * - worst score is the worst products at the worst locations
     *
     * #param string $dimension Dimension
     * @param Dimension $dimension Dimension
     *
     * @return array
     */
    private function getScoreRange($dimension)
    {
        $bestScore  = $this->getBestScore($dimension);
        $worstScore = $this->getWorstScore($dimension);

        return ['min' => $worstScore, 'max' => $bestScore];
    }

    /**
     * Return the score associated with the best positioning of products according to a given dimension.
     * That is: the products with the best individual score at the best positions.
     *
     * #param string $dimension Dimension
     * @param Dimension $dimension Dimension
     *
     * @return float
     */
    private function getBestScore($dimension)
    {
        $dimensionField = $dimension->getDocumentField();

        $collection = $this->getBaseProductCollection();
        $collection->setPageSize($this->sampleSize);
        $collection->setOrder($dimensionField, SortOrderInterface::SORT_DESC);

        return $this->getCollectionScore($dimension, $collection);
    }

    /**
     * Return the score associated with the best positioning of products according to a given dimension.
     * That is: the products with the worst individual score at the best positions.
     *
     * #param string $dimension Dimension
     * @param Dimension $dimension Dimension
     *
     * @return float
     */
    private function getWorstScore($dimension)
    {
        $dimensionField = $dimension->getDocumentField();

        $collection = $this->getBaseProductCollection();
        $collection->setPageSize($this->sampleSize);
        $collection->setOrder($dimensionField, SortOrderInterface::SORT_ASC);

        return $this->getCollectionScore($dimension, $collection);
    }

    /**
     * Compute the score of a products collection according to a given dimension.
     *
     * #param string     $dimension  Dimension.
     * @param Dimension  $dimension  Dimension
     * @param Collection $collection Products collection.
     *
     * @return float
     */
    private function getCollectionScore($dimension, $collection)
    {
        $score = (float) 0;

        $position = 0;
        foreach ($collection as $product) {
            if (!isset($this->scoreCoefficients[$position])) {
                break;
            }
            $productDimension = $dimension->getProductValue($product->getDocumentSource() ?? []);
            $score += $this->scoreCoefficients[$position] * $productDimension;
            $position++;
        }

        return $score;
    }

    /**
     * According to a given dimension, extract individual products score and
     * - determine the global score computed on the sample size
     * - extract each product dimension value and the percent of that value against the maximum value in the sample size
     *
     * @param Dimension $dimension Dimension.
     *
     * @return array
     */
    private function getProductsScore($dimension)
    {
        $globalScore = 0;
        $productValues = [];
        $max = 0;

        $products = $this->getCurrentProducts();
        $position = 0;
        foreach ($products as $product) {
            $productDimension = $dimension->getProductValue($product->getDocumentSource() ?? []);
            if (isset($this->scoreCoefficients[$position])) {
                $globalScore += $this->scoreCoefficients[$position] * $productDimension;
            }
            $productValues[$product->getId()] = [
                'value'     => $productDimension,
                'percent'   => 0,
            ];
            $max = ($productDimension > $max) ? $productDimension : $max;
            $position++;
        }

        if ($max > 0) {
            foreach ($productValues as &$product) {
                $product['percent'] = $product['value'] * (100.0 / $max);
            }
        }

        return ['global' => $globalScore, 'products' => $productValues];
    }

    /**
     * Return products visible in the preview, taking into account
     * - the blacklisted products
     * - the manually sorted products
     * the returned array contains
     * - first the sorted (and non-blacklisted) products in the correct order
     * - then the rest of the non-blacklisted products
     * - and finally the blacklisted products
     *
     * @return array
     */
    private function getCurrentProducts()
    {
        $sortedProducts = $this->getSortedProducts();

        // Collection without neither the blacklisted products nor the manually sorted products.
        $collection = $this->removeBlacklistedProducts($this->getBaseProductCollection());
        $collection = $this->removeSortedProducts($collection);
        // Using the full pagesize/preview size.
        $collection->setPageSize($this->pageSize);
        $automaticProducts = $collection->getItems();

        $products = $sortedProducts + $automaticProducts;
        $products = array_slice($products, 0, $this->pageSize, true);

        $products = $products + $this->getBlacklistedProducts();

        return $products;
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

        $collection->setStoreId($this->searchContext->getStoreId())
            ->addAttributeToSelect(['name']);

        return $this->prepareBaseProductCollection($collection);
    }

    /**
     * Return the sorted (and non-blacklisted) products in the correct order.
     *
     * @return ProductInterface[]
     */
    private function getSortedProducts()
    {
        $sortedProducts = [];

        $blacklistedIds = $this->getBlacklistedProductIds();
        $sortedIds      = $this->getSortedProductIds();
        $sortedIds      = array_diff($sortedIds, $blacklistedIds);
        if (!empty($sortedIds)) {
            $sortedProducts = $this->getSpecificProducts($sortedIds);

            // Enforce local sort order.
            $sortedProducts = array_replace(array_flip($sortedIds), $sortedProducts);
        }

        return $sortedProducts;
    }

    /**
     * Return the blacklisted products in no particular order.
     *
     * @return ProductInterface[]
     */
    private function getBlacklistedProducts()
    {
        $blacklistedProducts = $this->getSpecificProducts($this->getBlacklistedProductIds());

        return $blacklistedProducts;
    }

    /**
     * Return a list of specific products and their statistical data as an array.
     *
     * @param array $productIds Product Ids.
     *
     * @return ProductInterface[]
     */
    private function getSpecificProducts($productIds = [])
    {
        $products = [];

        if (!empty($productIds)) {
            $collection = $this->collectionFactory->create();
            $collection->setStoreId($this->searchContext->getStoreId())
                ->setVisibility($this->visibility)
                ->addAttributeToSelect(['name']);

            $queryParams = [];
            $queryParams['must'][] = $this->getEntityIdFilterQuery(array_values($productIds));
            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryParams);
            $collection->addQueryFilter($query);

            $products = $collection->getItems();
        }

        return $products;
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
        $blacklistedProducts = $this->getBlacklistedProductIds();
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
        $sortedProductIds = $this->getSortedProductIds();
        if (!empty($sortedProductIds)) {
            $queryParams = [];
            $queryParams['mustNot'][] = $this->getEntityIdFilterQuery(array_values($sortedProductIds));

            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryParams);
            $collection->addQueryFilter($query);
        }

        return $collection;
    }
}
