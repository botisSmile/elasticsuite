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

namespace Smile\ElasticsuiteAbCampaign\Cron;

use Smile\ElasticsuiteAbCampaign\Api\CampaignAggregatorInterface;

/**
 * Cron task used to aggregate campaign data in DB.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class CampaignAggregator
{
    /**
     * @var CampaignAggregatorInterface
     */
    private $campaignAggregator;

    /**
     * CampaignAggregator constructor.
     *
     * @param CampaignAggregatorInterface $campaignAggregator Campaign aggregator.
     */
    public function __construct(
        CampaignAggregatorInterface $campaignAggregator
    ) {
        $this->campaignAggregator   = $campaignAggregator;
    }

    /**
     * Run campaign data aggregation.
     *
     * @return void
     */
    public function execute(): void
    {
        $this->campaignAggregator->aggregateCampaignData();
    }
}
