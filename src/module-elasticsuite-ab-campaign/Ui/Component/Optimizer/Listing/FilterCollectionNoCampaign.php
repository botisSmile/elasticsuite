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

use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteExplain\Ui\Component\Optimizer\Listing\OptimizerCollectionProcessorInterface;

/**
 * Filter optimizer collection to have only optimizer not linked to a campaign..
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class FilterCollectionNoCampaign implements OptimizerCollectionProcessorInterface
{
    /**
     * @var CampaignOptimizerResource
     */
    private $campaignOptimizerResource;

    /**
     * FilterCollectionNoCampaign constructor.
     *
     * @param CampaignOptimizerResource $campaignOptimizerResource Campaign optimizer resource
     */
    public function __construct(CampaignOptimizerResource $campaignOptimizerResource)
    {
        $this->campaignOptimizerResource = $campaignOptimizerResource;
    }

    /**
     * {@inheritdoc}
     */
    public function process(OptimizerCollection $collection)
    {
        $this->campaignOptimizerResource->joinCampaignToOptimizerCollection($collection);
        $collection->getSelect()->where('campaign_optimizer.campaign_id IS NULL');
    }
}
