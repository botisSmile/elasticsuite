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

namespace Smile\ElasticsuiteAbCampaign\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CampaignSearchResultsInterface
 *
 * @¢ategory Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
interface CampaignSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get campaigns.
     *
     * @return CampaignInterface[]
     */
    public function getItems();

    /**
     * Set campaigns.
     *
     * @param CampaignInterface[] $items Campaigns.
     * @return self
     */
    public function setItems(array $items);
}
