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

namespace Smile\ElasticsuiteMerchandisingGauge\Model\Dimension;

use Smile\ElasticsuiteMerchandisingGauge\Model\Dimension;
use Smile\ElasticsuiteBehavioralData\Model\Config as BehavioralDataConfig;

/**
 * Weekly stats based dimension.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class WeeklyBased extends Dimension
{
    /**
     * @var BehavioralDataConfig
     */
    private $behavioralConfig;

    /**
     * WeeklyBased constructor.
     *
     * @param string               $identifier        Dimension identifier.
     * @param string               $label             Dimension label.
     * @param string               $valueLabelPattern Value display pattern.
     * @param string               $documentField     Document field name.
     * @param BehavioralDataConfig $behavioralConfig  Behavioral data config.
     */
    public function __construct(
        $identifier,
        $label,
        $valueLabelPattern,
        $documentField,
        BehavioralDataConfig $behavioralConfig
    ) {
        parent::__construct($identifier, $label, $valueLabelPattern, $documentField);
        $this->behavioralConfig = $behavioralConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregationMetrics()
    {
        $metrics = [];

        if ($this->behavioralConfig->isUseWeeklyStats()) {
            $metrics = parent::getAggregationMetrics();
        }

        return $metrics;
    }

    /**
     * {@inheritDoc}
     */
    public function isUsable($metricsData)
    {
        $usable = false;

        if ($this->behavioralConfig->isUseWeeklyStats()) {
            $usable = parent::isUsable($metricsData);
        }

        return $usable;
    }

    /**
     * {@inheritDoc}
     */
    public function getProductValue($documentSource)
    {
        $value = (float) 0;

        if ($this->behavioralConfig->isUseWeeklyStats()) {
            $value = parent::getProductValue($documentSource);
        }

        return $value;
    }
}
