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

namespace Smile\ElasticsuiteMerchandisingGauge\Search\Request\Product\Aggregation\Provider;

use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\MetricInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfiguration\AggregationProviderInterface;

/**
 * Merchandising metrics provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class MerchandisingMetricsProvider implements AggregationProviderInterface
{
    const BASE_AGGREGATION = '_type';

    const METRICS_CONTAINER = 'product';

    const TOTAL_SALES       = 'total_sales';
    const DAILY_SALES       = 'daily_sales';
    const WEEKLY_SALES      = 'weekly_sales';

    const TOTAL_VIEWS       = 'total_views';
    const DAILY_VIEWS       = 'daily_views';
    const WEEKLY_VIEWS      = 'weekly_views';


    /* Sales metrics */
    /* Total sales */
    const TOTAL_SALES_STATS             = 'total_sales_stats';
    const TOTAL_SALES_EXTENDED_STATS    = 'total_sales_extended_stats';
    const TOTAL_SALES_PERCENTILES       = 'total_sales_percentiles';
    /* Daily sales moving average */
    const DAILY_SALES_STATS             = 'daily_sales_stats';
    const DAILY_SALES_EXTENDED_STATS    = 'daily_sales_extended_stats';
    const DAILY_SALES_PERCENTILES       = 'daily_sales_percentiles';
    /* Weekly sales moving average */
    const WEEKLY_SALES_STATS            = 'weekly_sales_stats';
    const WEEKLY_SALES_EXTENDED_STATS   = 'weekly_sales_extended_stats';
    const WEEKLY_SALES_PERCENTILES      = 'weekly_sales_percentiles';

    /* Views metrics */
    /* Total views */
    const TOTAL_VIEWS_STATS             = 'total_views_stats';
    const TOTAL_VIEWS_EXTENDED_STATS    = 'total_views_extended_stats';
    const TOTAL_VIEWS_PERCENTILES       = 'total_views_percentiles';
    /* Daily views moving average */
    const DAILY_VIEWS_STATS             = 'daily_views_stats';
    const DAILY_VIEWS_EXTENDED_STATS    = 'daily_views_extended_stats';
    const DAILY_VIEWS_PERCENTILES       = 'daily_views_percentiles';
    /* Weekly views moving average */
    const WEEKLY_VIEWS_STATS            = 'weekly_views_stats';
    const WEEKLY_VIEWS_EXTENDED_STATS   = 'weekly_views_extended_stats';
    const WEEKLY_VIEWS_PERCENTILES      = 'weekly_views_percentiles';

    /* Sales count fields */
    const TOTAL_SALES_FIELD             = '_stats.sales.total';
    const DAILY_SALES_FIELD             = '_stats.sales.daily.ma';
    const WEEKLY_SALES_FIELD            = '_stats.sales.weekly.ma';

    /* Views count fields */
    const TOTAL_VIEWS_FIELD             = '_stats.views.total';
    const DAILY_VIEWS_FIELD             = '_stats.views.daily.ma';
    const WEEKLY_VIEWS_FIELD            = '_stats.views.weekly.ma';

    /**
     * Metrics to field mapping
     *
     * @var array
     */
    private $fieldMapping = [
        /* Total sales */
        self::TOTAL_SALES_STATS             => self::TOTAL_SALES_FIELD,
        self::TOTAL_SALES_EXTENDED_STATS    => self::TOTAL_SALES_FIELD,
        self::TOTAL_SALES_PERCENTILES       => self::TOTAL_SALES_FIELD,
        /* Daily sales moving average */
        self::DAILY_SALES_STATS             => self::DAILY_SALES_FIELD,
        self::DAILY_SALES_EXTENDED_STATS    => self::DAILY_SALES_FIELD,
        self::DAILY_SALES_PERCENTILES       => self::DAILY_SALES_FIELD,
        /* Weekly sales moving average */
        self::WEEKLY_SALES_STATS            => self::WEEKLY_SALES_FIELD,
        self::WEEKLY_SALES_EXTENDED_STATS   => self::WEEKLY_SALES_FIELD,
        self::WEEKLY_SALES_PERCENTILES      => self::WEEKLY_SALES_FIELD,

        self::TOTAL_VIEWS_STATS             => self::TOTAL_VIEWS_FIELD,
        self::TOTAL_VIEWS_EXTENDED_STATS    => self::TOTAL_VIEWS_FIELD,
        self::TOTAL_VIEWS_PERCENTILES       => self::TOTAL_VIEWS_FIELD,
        /* Daily views moving average */
        self::DAILY_VIEWS_STATS             => self::DAILY_VIEWS_FIELD,
        self::DAILY_VIEWS_EXTENDED_STATS    => self::DAILY_VIEWS_FIELD,
        self::DAILY_VIEWS_PERCENTILES       => self::DAILY_VIEWS_FIELD,
        /* Weekly views moving average */
        self::WEEKLY_VIEWS_STATS            => self::WEEKLY_VIEWS_FIELD,
        self::WEEKLY_VIEWS_EXTENDED_STATS   => self::WEEKLY_VIEWS_FIELD,
        self::WEEKLY_VIEWS_PERCENTILES      => self::WEEKLY_VIEWS_FIELD,
    ];

    /**
     * Dimension to field mapping
     *
     * @var array
     */
    private $dimensionFieldMapping = [
        self::TOTAL_SALES   => self::TOTAL_SALES_FIELD,
        self::DAILY_SALES   => self::DAILY_SALES_FIELD,
        self::WEEKLY_SALES  => self::WEEKLY_SALES_FIELD,
        self::TOTAL_VIEWS   => self::TOTAL_VIEWS_FIELD,
        self::DAILY_VIEWS   => self::DAILY_VIEWS_FIELD,
        self::WEEKLY_VIEWS  => self::WEEKLY_VIEWS_FIELD,
    ];

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory
     */
    private $metricFactory;

    /**
     * @var \Smile\ElasticsuiteBehavioralData\Model\Config
     */
    private $behavioralDataConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * Constructor.
     *
     * @param \Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory $metricFactory        Metric factory.
     * @param \Smile\ElasticsuiteBehavioralData\Model\Config                   $behavioralDataConfig Behavioral data config.
     * @param \Magento\Framework\App\State                                     $appState             Application state.
     */
    public function __construct(
        \Smile\ElasticsuiteCore\Search\Request\Aggregation\MetricFactory $metricFactory,
        \Smile\ElasticsuiteBehavioralData\Model\Config $behavioralDataConfig,
        \Magento\Framework\App\State $appState
    ) {
        $this->metricFactory = $metricFactory;
        $this->behavioralDataConfig = $behavioralDataConfig;
        $this->appState = $appState;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(
        $storeId,
        $query = null,
        $filters = [],
        $queryFilters = []
    ) {
        $aggregations = [];

        if ($this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $aggregations[self::METRICS_CONTAINER] = [
                'name' => self::BASE_AGGREGATION,
                'type' => BucketInterface::TYPE_TERM,
                'size' => 0,
                'metrics' => $this->getMetrics(),
            ];
        }

        /*
            "total_views_histogram" :{
                "histogram" : {
                    "field" : "_stats.views.total",
                    "min_doc_count": 1,
                    "missing": 0,
                    "interval": 1
                }
            },
         */

        return $aggregations;
    }

    /**
     * Get the corresponding field for a given metric
     *
     * @param string $metric Metric name.
     *
     * @return string|null
     */
    public function getMetricRelatedField($metric)
    {
        $field = $this->fieldMapping[$metric] ?? null;

        return $field;
    }

    /**
     * Get the corresponding ES field for a given dimension
     *
     * @param string $dimension Dimension name
     *
     * @return string|null
     */
    public function getDimensionRelatedField($dimension)
    {
        $field = $this->dimensionFieldMapping[$dimension] ?? null;

        return $field;
    }

    /**
     * Get the corresponding field data path for a given dimension
     *
     * @param string $dimension Dimension name
     *
     * @return string|null
     */
    public function getDimensionRelatedFieldPath($dimension)
    {
        $fieldPath = null;

        if ($field = $this->getDimensionRelatedField($dimension)) {
            $fieldPath = 'document_source/' . str_replace('.', '/', $field);
        }

        return $fieldPath;
    }

    /**
     * Return metrics aggregations to apply to the main bucket aggregation.
     *
     * @return array
     */
    private function getMetrics()
    {
        $metrics = array_merge($this->getSalesMetrics(), $this->getViewsMetrics());

        return $metrics;
    }

    /**
     * Get sales related aggregation metrics
     *
     * @return array
     */
    private function getSalesMetrics()
    {
        $metrics = [];

        /* Total sales */
        $metrics[] = [
            'name' => self::TOTAL_SALES_STATS,
            'type' => MetricInterface::TYPE_STATS,
            'field' => self::TOTAL_SALES_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::TOTAL_SALES_EXTENDED_STATS,
            'type' => MetricInterface::TYPE_EXTENDED_STATS,
            'field' => self::TOTAL_SALES_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::TOTAL_SALES_PERCENTILES,
            'type' => MetricInterface::TYPE_PERCENTILES,
            'field' => self::TOTAL_SALES_FIELD,
            'config' => ['missing' => 0],
        ];
        /* Daily sales moving average */
        $metrics[] = [
            'name' => self::DAILY_SALES_STATS,
            'type' => MetricInterface::TYPE_STATS,
            'field' => self::DAILY_SALES_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::DAILY_SALES_EXTENDED_STATS,
            'type' => MetricInterface::TYPE_EXTENDED_STATS,
            'field' => self::DAILY_SALES_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::DAILY_SALES_PERCENTILES,
            'type' => MetricInterface::TYPE_PERCENTILES,
            'field' => self::DAILY_SALES_FIELD,
            'config' => ['missing' => 0],
        ];
        /* Weekly sales moving average */
        if ($this->behavioralDataConfig->isUseWeeklyStats()) {
            $metrics[] = [
                'name' => self::WEEKLY_SALES_STATS,
                'type' => MetricInterface::TYPE_STATS,
                'field' => self::WEEKLY_SALES_FIELD,
                'config' => ['missing' => 0],
            ];
            $metrics[] = [
                'name' => self::WEEKLY_SALES_EXTENDED_STATS,
                'type' => MetricInterface::TYPE_EXTENDED_STATS,
                'field' => self::WEEKLY_SALES_FIELD,
                'config' => ['missing' => 0],
            ];
            $metrics[] = [
                'name' => self::WEEKLY_SALES_PERCENTILES,
                'type' => MetricInterface::TYPE_PERCENTILES,
                'field' => self::WEEKLY_SALES_FIELD,
                'config' => ['missing' => 0],
            ];
        }

        return $metrics;
    }

    /**
     * Get views related aggregation metrics
     *
     * @return array
     */
    private function getViewsMetrics()
    {
        $metrics = [];

        /* Total views */
        $metrics[] = [
            'name' => self::TOTAL_VIEWS_STATS,
            'type' => MetricInterface::TYPE_STATS,
            'field' => self::TOTAL_VIEWS_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::TOTAL_VIEWS_EXTENDED_STATS,
            'type' => MetricInterface::TYPE_EXTENDED_STATS,
            'field' => self::TOTAL_VIEWS_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::TOTAL_VIEWS_PERCENTILES,
            'type' => MetricInterface::TYPE_PERCENTILES,
            'field' => self::TOTAL_VIEWS_FIELD,
            'config' => ['missing' => 0],
        ];
        /* Daily views moving average */
        $metrics[] = [
            'name' => self::DAILY_VIEWS_STATS,
            'type' => MetricInterface::TYPE_STATS,
            'field' => self::DAILY_VIEWS_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::DAILY_VIEWS_EXTENDED_STATS,
            'type' => MetricInterface::TYPE_EXTENDED_STATS,
            'field' => self::DAILY_VIEWS_FIELD,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name' => self::DAILY_VIEWS_PERCENTILES,
            'type' => MetricInterface::TYPE_PERCENTILES,
            'field' => self::DAILY_VIEWS_FIELD,
            'config' => ['missing' => 0],
        ];
        /* Weekly views moving average */
        if ($this->behavioralDataConfig->isUseWeeklyStats()) {
            $metrics[] = [
                'name' => self::WEEKLY_VIEWS_STATS,
                'type' => MetricInterface::TYPE_STATS,
                'field' => self::WEEKLY_VIEWS_FIELD,
                'config' => ['missing' => 0],
            ];
            $metrics[] = [
                'name' => self::WEEKLY_VIEWS_EXTENDED_STATS,
                'type' => MetricInterface::TYPE_EXTENDED_STATS,
                'field' => self::WEEKLY_VIEWS_FIELD,
                'config' => ['missing' => 0],
            ];
            $metrics[] = [
                'name' => self::WEEKLY_VIEWS_PERCENTILES,
                'type' => MetricInterface::TYPE_PERCENTILES,
                'field' => self::WEEKLY_VIEWS_FIELD,
                'config' => ['missing' => 0],
            ];
        }

        return $metrics;
    }
}
