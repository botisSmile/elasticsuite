<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralData
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBehavioralData\Model\ResourceModel\Product\Indexer\Fulltext\Datasource;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Response\Aggregation;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteCore\Model\ResourceModel\Indexer\AbstractIndexer;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\MetricInterface;
use Smile\ElasticsuiteCore\Search\Request\PipelineInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\Builder as RequestBuilder;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\PipelineFactory;

/**
 * Behavioral Data Index resource model
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralData
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class BehavioralData extends AbstractIndexer
{
    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\Framework\Search\SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory
     */
    private $aggregationFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory
     */
    private $metricFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Aggregation\PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        SearchEngineInterface $searchEngine,
        RequestBuilder $requestBuilder,
        AggregationFactory $aggregationFactory,
        MetricFactory $metricFactory,
        PipelineFactory $pipelineFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($resource, $storeManager);
        $this->queryFactory       = $queryFactory;
        $this->searchEngine       = $searchEngine;
        $this->requestBuilder     = $requestBuilder;
        $this->aggregationFactory = $aggregationFactory;
        $this->metricFactory      = $metricFactory;
        $this->pipelineFactory    = $pipelineFactory;
        $this->logger             = $logger;
    }

    /**
     * Load behavioral data for a list of product ids and a given store.
     *
     * @param integer $storeId    Store id.
     * @param array   $productIds Product ids list.
     *
     * @return array
     */
    public function loadBehavioralData($storeId, $productIds)
    {
        $data = [];

        try {
            $views = $this->getViewsData($storeId, $productIds);
            $sales = $this->getSalesData($storeId, $productIds);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $data;
    }

    /**
     * @param string $page The page to filter on
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getPageFilter($page)
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'page.type.identifier', 'value' => $page]
        );
    }

    private function getViewsData($storeId, $productIds)
    {
        $productIdsFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERMS,
            ['field' => 'page.product.id', 'values' => $productIds]
        );

        $queryFilter = $this->queryFactory->create(
            QueryInterface::TYPE_BOOL,
            ['must' => [$productIdsFilter, $this->getPageFilter('catalog_product_view')]]
        );

        $dailyMaAgg = $this->getMovingAverageByDate(
            'daily_views_count',
            'ma_daily',
            '1d',
            7,
            '30day'
        );

        $weeklyMaAgg = $this->getMovingAverageByDate(
            'weekly_views_count',
            'ma_weekly',
            '7d',
            4,
            '60day'
        );

        $productIdAgg = $this->aggregationFactory->create(
            BucketInterface::TYPE_TERM,
            [
                'field'        => 'page.product.id',
                'name'         => 'product_id',
                'childBuckets' => [$dailyMaAgg, $weeklyMaAgg],
            ]
        );

        $request = $this->requestBuilder->create(
            $storeId,
            'tracking_log_event',
            0,
            0,
            $queryFilter,
            [],
            [],
            [],
            [$productIdAgg]
        );

        $result = $this->searchEngine->search($request);
    }

    private function getSalesData($storeId, $productIds)
    {
        $productIdFilter = $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            [
                'path'  => 'page.order.items',
                'query' => $this->queryFactory->create(
                    QueryInterface::TYPE_TERMS,
                    ['field' => 'page.order.items.product_id', 'values' => $productIds]
                ),
            ]
        );

        $queryFilter = $this->queryFactory->create(
            QueryInterface::TYPE_BOOL,
            ['must' => [$productIdFilter, $this->getPageFilter('checkout_onepage_success')]]
        );

        $dailyMaAgg = $this->getMovingAverageByDate(
            'daily_sales_count',
            'ma_daily',
            '1d',
            7,
            '30day'
        );

        $rnDailyAgg = $this->aggregationFactory->create(
            'reverseNestedBucket',
            [
                'name'         => 'rn_daily_sales_count',
                'field'        => null,
                'childBuckets' => [$dailyMaAgg],
            ]
        );

        $weeklyMaAgg = $this->getMovingAverageByDate(
            'weekly_sales_count',
            'ma_weekly',
            '7d',
            4,
            '60day'
        );

        $rnWeeklyAgg = $this->aggregationFactory->create(
            'reverseNestedBucket',
            [
                'name'         => 'rn_weekly_sales_count',
                'field'        => null,
                'childBuckets' => [$weeklyMaAgg],
            ]
        );

        $productIdAgg = $this->aggregationFactory->create(
            BucketInterface::TYPE_TERM,
            [
                'field'        => 'page.order.items.product_id',
                'nestedPath'   => 'page.order.items',
                'name'         => 'product_id',
                'childBuckets' => [$rnDailyAgg, $rnWeeklyAgg],
            ]
        );

        $request = $this->requestBuilder->create(
            $storeId,
            'tracking_log_event',
            0,
            0,
            $queryFilter,
            [],
            [],
            [],
            [$productIdAgg]
        );

        $result = $this->searchEngine->search($request);
    }

    /**
     * Create a moving function pipeline.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-movfn-aggregation.html
     *
     * @param string $aggregationName The aggregation name
     * @param string $maName          The moving average name
     * @param string $dateInterval    The interval to compute histogram on. ('1d', '1w', etc...)
     * @param int    $maWindow        The moving average window to calculate.
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\BucketInterface
     */
    private function getMovingAverageByDate($aggregationName, $maName, $dateInterval, $maWindow, $from)
    {
        $metrics = [
            $this->metricFactory->create(
                ['name' => 'event_count', 'field' => 'event_id', 'type' => MetricInterface::TYPE_CARDINALITY]
            ),
        ];

        $pipelines = [
            $this->pipelineFactory->create(
                'movingFunctionPipeline',
                [
                    'name'        => $maName,
                    'bucketsPath' => 'event_count',
                    'window'      => $maWindow,
                    'gapPolicy'   => PipelineInterface::GAP_POLICY_INSERT_ZEROS,
                    'script'      => 'MovingFunctions.unweightedAvg(values)',
                ]
            ),
        ];

        $aggregationParams = [
            'field'     => 'date',
            'name'      => $aggregationName,
            'interval'  => $dateInterval,
            'sortOrder' => [['_key' => 'asc']],
            'metrics'   => $metrics,
            'pipelines' => $pipelines,
        ];

        $now  = new \DateTime();
        $from = $now->modify(sprintf('-%s', $from));

        $aggregationParams['filter'] = $this->queryFactory->create(
            QueryInterface::TYPE_RANGE,
            [
                'field'  => 'date',
                'bounds' => ['gte' => $from->format(\Magento\Framework\DB\Adapter\Pdo\Mysql::DATETIME_FORMAT)],
            ]
        );

        return $this->aggregationFactory->create(
            BucketInterface::TYPE_DATE_HISTOGRAM,
            $aggregationParams
        );
    }
}
