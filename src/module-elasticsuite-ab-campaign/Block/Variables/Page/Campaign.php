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

namespace Smile\ElasticsuiteAbCampaign\Block\Variables\Page;

use Smile\ElasticsuiteTracker\Block\Variables\Page\AbstractBlock;

/**
 * Campaign variables block for page tracking, exposes all campaign tracking variables.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class Campaign extends AbstractBlock
{
    /**
     * Append the campaign data to the tracked variables list.
     *
     * @return array
     */
    public function getVariables(): array
    {
        $campaignId = 3;
        $scenario = 'A';

        return [
            'ab_campaign.id'       => $campaignId,
            'ab_campaign.scenario' => $scenario,
        ];
    }
}
