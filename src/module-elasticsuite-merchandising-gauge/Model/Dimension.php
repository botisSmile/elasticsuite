<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandiserGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Model;

use Smile\ElasticsuiteCore\Search\Request\MetricInterface;
use Magento\Framework\DataObject;

/**
 * Class Dimension
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandiserGauge
 */
class Dimension
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $valueLabelPattern;

    /**
     * @var string
     */
    protected $documentField;

    /**
     * @var string
     */
    protected $documentSourcePath;

    /**
     * AbstractDimension constructor.
     *
     * @param string $identifier        Dimension identifier.
     * @param string $label             Dimension label.
     * @param string $valueLabelPattern Value display pattern.
     * @param string $documentField     Document field name.
     */
    public function __construct(
        $identifier,
        $label,
        $valueLabelPattern,
        $documentField
    ) {
        $this->identifier   = $identifier;
        $this->label        = $label;
        $this->valueLabelPattern    = $valueLabelPattern;
        $this->documentField        = $documentField;
        $this->documentSourcePath   = str_replace('.', '/', $this->documentField);
    }

    /**
     * Get dimension identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get dimension label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get value label pattern.
     *
     * @return string
     */
    public function getValueLabelPattern()
    {
        return $this->valueLabelPattern;
    }

    /**
     * Get the document field containing the dimension data.
     *
     * @return string
     */
    public function getDocumentField()
    {
        return $this->documentField;
    }

    /**
     * Return aggregation metrics used to extract stats about the dimension.
     *
     * @return array
     */
    public function getAggregationMetrics()
    {
        $metrics = [];

        $metrics[] = [
            'name'  => sprintf("%s_stats", $this->identifier),
            'type'  => MetricInterface::TYPE_STATS,
            'field' => $this->documentField,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name'  => sprintf("%s_extended_stats", $this->identifier),
            'type'  => MetricInterface::TYPE_EXTENDED_STATS,
            'field' => $this->documentField,
            'config' => ['missing' => 0],
        ];
        $metrics[] = [
            'name'  => sprintf("%s_percentiles", $this->identifier),
            'type'  => MetricInterface::TYPE_PERCENTILES,
            'field' => $this->documentField,
            'config' => ['missing' => 0],
        ];

        return $metrics;
    }

    /**
     * Returns true if the dimension can be exploited according to the provided aggregation metrics result data.
     *
     * @param array $metricsData Aggregation metrics data.
     *
     * @return boolean
     */
    public function isUsable($metricsData)
    {
        $usable = false;

        if (!empty($metricsData)) {
            $statsMetrics = sprintf("%s_extended_stats", $this->identifier);
            $statistics = $metricsData[$statsMetrics] ?? [];
            if (!empty($statistics)) {
                $usable = ($statistics['count'] > 0) && ($statistics['avg'] > 0);
            }
        }

        return $usable;
    }

    /**
     * Extract the dimension value from a product document source.
     *
     * @param array $documentSource Product document source.
     *
     * @return float
     */
    public function getProductValue($documentSource)
    {
        $value = (float) 0;

        if (!empty($documentSource)) {
            $data = new DataObject($documentSource);
            $value = (float) $data->getData($this->documentSourcePath) ?? 0;
        }

        return $value;
    }
}
