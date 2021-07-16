<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteAbCampaign\Model\Search\Usage\Campaign;

use Smile\ElasticsuiteAbCampaign\Model\Search\Campaign\KpiAggregator;

/**
 * Campaign KPI Report.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 */
class Report
{
    /**
     * @var KpiAggregator
     */
    protected $kpiAggregator;

    /**
     * @var array|null
     */
    protected $data = null;

    /**
     * Constructor.
     *
     * @param KpiAggregator $kpiAggregator Kpi aggregator.
     */
    public function __construct(
        KpiAggregator $kpiAggregator
    ) {
        $this->kpiAggregator = $kpiAggregator;
    }

    /**
     * Get report data.
     *
     * @return array
     */
    public function getData(): array
    {
        if ($this->data === null) {
            $this->data = $this->kpiAggregator->getData();
        }

        return $this->data;
    }
}
