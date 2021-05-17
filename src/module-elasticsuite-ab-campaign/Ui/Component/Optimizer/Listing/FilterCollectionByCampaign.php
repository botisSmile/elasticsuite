<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Optimizer\Listing;

use Magento\Framework\App\RequestInterface;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteExplain\Ui\Component\Optimizer\Listing\OptimizerCollectionProcessorInterface;

/**
 * Filter optimizer collection by campaign.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class FilterCollectionByCampaign implements OptimizerCollectionProcessorInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CampaignOptimizerResource
     */
    private $campaignOptimizerResource;

    /**
     * FilterCollectionByCampaign Constructor.
     *
     * @param RequestInterface          $request                   Request
     * @param CampaignOptimizerResource $campaignOptimizerResource Campaign optimizer resource
     */
    public function __construct(
        RequestInterface $request,
        CampaignOptimizerResource $campaignOptimizerResource
    ) {
        $this->request                   = $request;
        $this->campaignOptimizerResource = $campaignOptimizerResource;
    }

    /**
     * {@inheritdoc}
     */
    public function process(OptimizerCollection $collection)
    {
        $campaignId = (int) $this->request->getParam('campaign_id');
        $scenarioType = (string) $this->request->getParam('scenario_type');
        if ($scenarioType && $campaignId) {
            $this->campaignOptimizerResource->joinCampaignToOptimizerCollection($collection);
            $collection->getSelect()
                ->where('campaign_optimizer.campaign_id = ?', $campaignId)
                ->where('campaign_optimizer.scenario_type = ?', $scenarioType);
        }
    }
}
