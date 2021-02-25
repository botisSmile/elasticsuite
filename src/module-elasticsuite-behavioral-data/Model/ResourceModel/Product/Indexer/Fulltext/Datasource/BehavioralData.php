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

use Magento\Framework\Search\SearchEngineInterface;
use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\PipelineFactory;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\Builder as RequestBuilder;
use Smile\ElasticsuiteCore\Search\Request\MetricInterface;
use Smile\ElasticsuiteCore\Search\Request\PipelineInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Behavioral Data Index resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralData
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class BehavioralData
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
     * @var \Smile\ElasticsuiteBehavioralData\Model\Config
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var boolean|null
     */
    private $useWeeklyStats = null;

    /**
     * @var string|null
     */
    private $dailyFrom = null;

    /**
     * @var string|null
     */
    private $weeklyFrom = null;

    /**
     * BehavioralData constructor.
     *
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory             $queryFactory       Query Factory
     * @param \Magento\Framework\Search\SearchEngineInterface                       $searchEngine       Search Engine
     * @param \Smile\ElasticsuiteCore\Search\Request\Builder                        $requestBuilder     Request Builder
     * @param \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory $aggregationFactory Aggregation Factory
     * @param \Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory      $metricFactory      Metric Factory
     * @param \Smile\ElasticsuiteCore\Search\Request\Aggregation\PipelineFactory    $pipelineFactory    Pipeline Factory
     * @param \Psr\Log\LoggerInterface                                              $logger             Logger Interface
     * @param \Smile\ElasticsuiteBehavioralData\Model\Config                        $config             Module Configuration
     */
    public function __construct(
        QueryFactory $queryFactory,
        SearchEngineInterface $searchEngine,
        RequestBuilder $requestBuilder,
        AggregationFactory $aggregationFactory,
        MetricFactory $metricFactory,
        PipelineFactory $pipelineFactory,
        LoggerInterface $logger,
        \Smile\ElasticsuiteBehavioralData\Model\Config $config
    ) {
        $this->queryFactory       = $queryFactory;
        $this->searchEngine       = $searchEngine;
        $this->requestBuilder     = $requestBuilder;
        $this->aggregationFactory = $aggregationFactory;
        $this->metricFactory      = $metricFactory;
        $this->pipelineFactory    = $pipelineFactory;
        $this->config             = $config;
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

            // Not using array_merge_recursive here because it discards numeric keys (which are product ids).
            $data = array_replace_recursive($views, $sales);
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

    /**
     * Build a "from" filter for a given date.
     *
     * @param \DateTime $date A date
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getFromDateFilter(\DateTime $date)
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_RANGE,
            [
                'field'  => 'date',
                'bounds' => ['gte' => $date->format(\Magento\Framework\DB\Adapter\Pdo\Mysql::DATETIME_FORMAT)],
            ]
        );
    }

    /**
     * Compute views data for a given store Id and Product ids.
     *
     * @param int   $storeId    The store Id
     * @param int[] $productIds The product Ids
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getViewsData($storeId, $productIds)
    {
        $dailyFrom  = $this->getDailyFrom();
        $maxFrom    = $dailyFrom;
        $dailyMaAgg = $this->getMovingAverageByDate(
            'daily_views_count',
            'ma',
            '1d',
            7,
            $dailyFrom
        );

        $productIdAggParams = [
            'field'        => 'page.product.id',
            'name'         => 'product_id',
            'childBuckets' => [$dailyMaAgg],
        ];

        if ($this->isUseWeeklyStats()) {
            $weeklyFrom  = $this->getWeeklyFrom();
            $maxFrom     = $weeklyFrom;
            $weeklyMaAgg = $this->getMovingAverageByDate(
                'weekly_views_count',
                'ma',
                '7d',
                4,
                $weeklyFrom
            );

            $productIdAggParams['childBuckets'][] = $weeklyMaAgg;
        }

        $productIdAgg = $this->aggregationFactory->create(BucketInterface::TYPE_TERM, $productIdAggParams);

        $now        = new \DateTime();
        $from       = $now->modify(sprintf('-%s', $maxFrom));
        $dateFilter = $this->getFromDateFilter($from);

        $productIdsFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERMS,
            ['field' => 'page.product.id', 'values' => $productIds]
        );

        $queryFilter = $this->queryFactory->create(
            QueryInterface::TYPE_BOOL,
            ['must' => [$productIdsFilter, $dateFilter, $this->getPageFilter('catalog_product_view')]]
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

        return $this->parseResponse($result, 'views');
    }

    /**
     * Compute sales data for a given store Id and Product ids.
     *
     * @param int   $storeId    The store Id
     * @param int[] $productIds The product Ids
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getSalesData($storeId, $productIds)
    {
        $dailyFrom  = $this->getDailyFrom();
        $maxFrom    = $dailyFrom;
        $dailyMaAgg = $this->getMovingAverageByDate(
            'daily_sales_count',
            'ma',
            '1d',
            7,
            $dailyFrom
        );

        $rnDailyAgg = $this->aggregationFactory->create(
            BucketInterface::TYPE_REVERSE_NESTED,
            [
                'name'         => 'daily_sales_count',
                'field'        => null,
                'childBuckets' => [$dailyMaAgg],
            ]
        );

        $productIdAggParams = [
            'field'        => 'page.order.items.product_id',
            'nestedPath'   => 'page.order.items',
            'name'         => 'product_id',
            'childBuckets' => [$rnDailyAgg],
        ];

        if ($this->isUseWeeklyStats()) {
            $weeklyFrom  = $this->getWeeklyFrom();
            $maxFrom     = $weeklyFrom;

            $weeklyMaAgg = $this->getMovingAverageByDate(
                'weekly_sales_count',
                'ma',
                '7d',
                4,
                $weeklyFrom
            );

            $rnWeeklyAgg = $this->aggregationFactory->create(
                BucketInterface::TYPE_REVERSE_NESTED,
                [
                    'name'         => 'weekly_sales_count',
                    'field'        => null,
                    'childBuckets' => [$weeklyMaAgg],
                ]
            );

            $productIdAggParams['childBuckets'][] = $rnWeeklyAgg;
        }

        $productIdAgg = $this->aggregationFactory->create(BucketInterface::TYPE_TERM, $productIdAggParams);

        $now        = new \DateTime();
        $from       = $now->modify(sprintf('-%s', $maxFrom));
        $dateFilter = $this->getFromDateFilter($from);

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
            ['must' => [$productIdFilter, $dateFilter, $this->getPageFilter('checkout_onepage_success')]]
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

        return $this->parseResponse($result, 'sales');
    }

    /**
     * Compute behavioral data across query results.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponse $queryResponse Query Response
     * @param string                                                                     $eventType     Event Type
     *
     * @return array
     */
    private function parseResponse($queryResponse, $eventType)
    {
        $data         = [];
        $productIds   = $queryResponse->getAggregations()->getBucket('product_id');
        $dailyBucket  = sprintf('daily_%s_count', $eventType);
        $weeklyBucket = sprintf('weekly_%s_count', $eventType);

        if ($productIds) {
            /** @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\Aggregation\Value $childBucket */
            foreach ($productIds->getValues() as $childBucket) {
                $productId  = $childBucket->getValue();
                $eventCount = $childBucket->getMetrics() ? $childBucket->getMetrics()['count'] : false;
                $data[$productId]['_stats'][$eventType]['total'] = $eventCount;

                if ($childBucket->getAggregations()->getBucket($dailyBucket)) {
                    $subAggregation = $childBucket->getAggregations()->getBucket($dailyBucket);

                    if (!empty($subAggregation->getValues())) {
                        $values    = $subAggregation->getValues();
                        $lastValue = end($values);
                        $metrics   = $lastValue->getMetrics();
                        $metrics['ma'] = !is_array($metrics['ma']) ? $metrics['ma'] : 0;
                        $data[$productId]['_stats'][$eventType]['daily']['ma']    = $metrics['ma'];
                        $data[$productId]['_stats'][$eventType]['daily']['count'] = $metrics['event_count'] ?? false;
                    }
                }

                if ($this->isUseWeeklyStats()) {
                    if ($childBucket->getAggregations()->getBucket($weeklyBucket)) {
                        $subAggregation = $childBucket->getAggregations()->getBucket($weeklyBucket);
                        if (!empty($subAggregation->getValues())) {
                            $values    = $subAggregation->getValues();
                            $lastValue = end($values);
                            $metrics   = $lastValue->getMetrics();
                            $metrics['ma'] = !is_array($metrics['ma']) ? $metrics['ma'] : 0;
                            $data[$productId]['_stats'][$eventType]['weekly']['ma']    = $metrics['ma'];
                            $data[$productId]['_stats'][$eventType]['weekly']['count'] = $metrics['event_count'] ?? false;
                        }
                    }
                }
            }
        }

        return $data;
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
     * @param string $from            The maximum anterior delay to compute data from.
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
                PipelineInterface::TYPE_MOVING_FUNCTION,
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

        $aggregationParams['filter'] = $this->getFromDateFilter($from);

        return $this->aggregationFactory->create(
            BucketInterface::TYPE_DATE_HISTOGRAM,
            $aggregationParams
        );
    }

    /**
     * @return bool
     */
    private function isUseWeeklyStats()
    {
        if ($this->useWeeklyStats === null) {
            $this->useWeeklyStats = $this->config->isUseWeeklyStats();
        }

        return $this->useWeeklyStats;
    }

    /**
     * Get max anterior date to compute daily statistics.
     *
     * @return string
     */
    private function getDailyFrom()
    {
        if ($this->dailyFrom === null) {
            $this->dailyFrom = $this->config->getDailyFrom();
        }

        return $this->dailyFrom;
    }

    /**
     * Get max anterior date to compute weekly statistics.
     *
     * @return string
     */
    private function getWeeklyFrom()
    {
        if ($this->weeklyFrom === null) {
            $this->weeklyFrom = $this->config->getWeeklyFrom();
        }

        return $this->weeklyFrom;
    }
}
