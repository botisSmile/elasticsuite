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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CampaignSessionSearchResultsInterface.
 *
 * @¢ategory Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
interface CampaignSessionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get campaign sessions.
     *
     * @return CampaignSessionInterface[]
     */
    public function getItems();

    /**
     * Set campaign sessions.
     *
     * @param CampaignSessionInterface[] $items Campaign sessions.
     * @return self
     */
    public function setItems(array $items);
}
