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

namespace Smile\ElasticsuiteAbCampaign\Api;

use Exception;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Interface CampaignManagerInterface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
interface CampaignManagerInterface
{
    /**
     * Get unavailabitlies of a campaign.
     *
     * @param CampaignInterface|null $campaign Campaign
     * @return array
     */
    public function getUnavailabilities(?CampaignInterface $campaign): array;

    /**
     * Stop campaign.
     *
     * @param CampaignInterface $campaign Campaign
     * @throws Exception
     */
    public function stopCampaign(CampaignInterface $campaign);

    /**
     * Publish campaign.
     *
     * @param CampaignInterface $campaign Campaign
     * @throws Exception
     */
    public function publishCampaign(CampaignInterface $campaign);

    /**
     * Reopen campaign.
     *
     * @param CampaignInterface $campaign Campaign
     * @throws Exception
     */
    public function reopenCampaign(CampaignInterface $campaign);

    /**
     * Can publish campaign ?
     *
     * @param CampaignInterface $campaign Campaign
     * @return boolean
     */
    public function canPublish(CampaignInterface $campaign): bool;

    /**
     * Can reopen campaign ?
     *
     * @param CampaignInterface $campaign Campaign
     * @return boolean
     */
    public function canReopen(CampaignInterface $campaign): bool;

    /**
     * Can stop campaign ?
     *
     * @param CampaignInterface $campaign Campaign
     * @return boolean
     */
    public function canStop(CampaignInterface $campaign): bool;
}
