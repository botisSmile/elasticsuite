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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Search\Request\Product\Aggregation\Provider;

use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfiguration\AggregationProviderInterface;
use Smile\ElasticsuiteMerchandisingGauge\Model\DimensionProvider;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;

/**
 * Simpler Merchandising metrics provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class MerchandisingMetricsProvider implements AggregationProviderInterface
{
    const BASE_AGGREGATION = '_type';

    const METRICS_CONTAINER = 'product';

    /**
     * @var DimensionProvider
     */
    private $dimensionProvider;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * Constructor.
     *
     * @param DimensionProvider $dimensionProvider Dimension provider.
     * @param AppState          $appState          Application state.
     */
    public function __construct(
        DimensionProvider $dimensionProvider,
        AppState $appState
    ) {
        $this->dimensionProvider = $dimensionProvider;
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

        if ($this->appState->getAreaCode() === AppArea::AREA_ADMINHTML) {
            $aggregations[self::METRICS_CONTAINER] = [
                'name' => self::BASE_AGGREGATION,
                'type' => BucketInterface::TYPE_TERM,
                'size' => 0,
                'metrics' => $this->getMetrics(),
            ];
        }

        return $aggregations;
    }

    /**
     * Return metrics aggregations to apply to the main bucket aggregation.
     *
     * @return array
     */
    private function getMetrics()
    {
        $metrics = $this->dimensionProvider->getAggregationMetrics();

        return $metrics;
    }
}
