<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml;

use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Campaign Context
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Campaign
{
    /**
     * @var CampaignInterface|null
     */
    protected $currentCampaign;

    /**
     * Get current campaign.
     *
     * @return CampaignInterface|null
     */
    public function getCurrentCampaign(): ?CampaignInterface
    {
        return $this->currentCampaign;
    }

    /**
     * Set current campaign.
     *
     * @param CampaignInterface $campaign Campaign
     * @return $this
     */
    public function setCurrentCampaign(CampaignInterface $campaign): Campaign
    {
        $this->currentCampaign = $campaign;

        return $this;
    }
}
