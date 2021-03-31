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

use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;

/**
 * Campaign default data validator.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Date extends AbstractDate
{
    /**
     * {@inheritDoc}
     */
    public function validate(CampaignInterface $campaign)
    {
        parent::validate($campaign);
        $startDate = $campaign->getStartDate();
        $endDate   = $campaign->getEndDate();
        $now       = $this->filterDate(new \DateTime('now'));

        if ($startDate > $endDate) {
            throw new ValidatorException(__('End Date must follow Start Date.'));
        }

        if ($endDate < $now) {
            throw new ValidatorException(__('End Date cannot be defined before the date of today.'));
        }
    }
}
