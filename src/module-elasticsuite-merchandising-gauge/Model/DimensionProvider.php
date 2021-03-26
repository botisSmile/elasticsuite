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

namespace Smile\ElasticsuiteMerchandisingGauge\Model;

use Smile\ElasticsuiteMerchandisingGauge\Model\Dimension;

/**
 * Class DimensionProvider
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class DimensionProvider
{
    /**
     * @var Dimension[]
     */
    private $dimensions;

    /**
     * DimensionProvider constructor.
     *
     * @param Dimension[] $dimensions Dimensions
     */
    public function __construct(
        $dimensions = []
    ) {
        $this->dimensions = $dimensions;
    }

    /**
     * Return aggregation metrics related to the dimensions.
     *
     * @return array
     */
    public function getAggregationMetrics()
    {
        $metrics = [];

        foreach ($this->dimensions as &$dimension) {
            $metrics = array_merge($metrics, $dimension->getAggregationMetrics());
        }

        return $metrics;
    }

    /**
     * Returns the list of dimensions that can be exploited according to the provided aggregation metrics result data.
     *
     * @param array $metricsData Aggregation metrics data.
     *
     * @return Dimension[]
     */
    public function getUsableDimensions($metricsData)
    {
        $dimensions = [];

        foreach ($this->dimensions as &$dimension) {
            if ($dimension->isUsable($metricsData)) {
                $dimensions[$dimension->getIdentifier()] = $dimension;
            }
        }

        return $dimensions;
    }
}
