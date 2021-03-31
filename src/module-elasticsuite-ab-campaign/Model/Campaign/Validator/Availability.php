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

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign\Validator;

use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;

/**
 * Campaign default data validator.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Availability extends AbstractDate
{
    /**
     * @var CampaignManagerInterface
     */
    private $campaignManager;

    /**
     * Availability validator constructor.
     *
     * @param DateFilter               $dateFilter      Date filter
     * @param CampaignManagerInterface $campaignManager Campaign manager
     */
    public function __construct(DateFilter $dateFilter, CampaignManagerInterface $campaignManager)
    {
        parent::__construct($dateFilter);
        $this->campaignManager = $campaignManager;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(CampaignInterface $campaign)
    {
        parent::validate($campaign);
        $startDate = $campaign->getStartDate();
        $endDate   = $campaign->getEndDate();
        $unavailabilities = $this->campaignManager->getUnavailabilities($campaign);
        foreach ($unavailabilities as $unavailability) {
            $startUnavailability = $unavailability['start_date'];
            $endUnavailability = $unavailability['end_date'];
            $doesNotOverlap = $endDate < $startUnavailability || $startDate > $endUnavailability;
            if (!$doesNotOverlap) {
                throw new ValidatorException(
                    __("You can't save or publish a campaign over a period with already a published campaign")
                );
            }
        }
    }
}
