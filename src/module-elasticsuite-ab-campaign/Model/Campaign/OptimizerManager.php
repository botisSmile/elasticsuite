<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign;

use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;

/**
 * Class OptimizerManager
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class OptimizerManager implements OptimizerManagerInterface
{
    /**
     * @var CampaignOptimizerResource
     */
    private $campaignOptimizerResource;

    /**
     * OptimizerManager constructor.
     *
     * @param CampaignOptimizerResource $campaignOptimizerResource Campaign optimizer resource
     */
    public function __construct(
        CampaignOptimizerResource $campaignOptimizerResource
    ) {
        $this->campaignOptimizerResource = $campaignOptimizerResource;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * {@inheritdoc}
     */
    public function extractOptimizerIdsToRestrain(
        array $optimizerIds,
        bool $takeInAccountCampaignStatus = false,
        bool $takeInAccountCampaignDates = false
    ): array {
        $filterByCampaignStatus = $takeInAccountCampaignStatus ? [CampaignInterface::STATUS_PUBLISHED] : [];

        return $this->campaignOptimizerResource->extractOptimizerIdsLinkedToCampaign(
            $optimizerIds,
            $filterByCampaignStatus,
            $takeInAccountCampaignDates
        );
    }
}
