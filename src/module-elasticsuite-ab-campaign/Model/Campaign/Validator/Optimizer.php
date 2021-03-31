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

use Exception;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\ValidatorInterface;

/**
 * Campaign optimizer validator.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Optimizer implements ValidatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(CampaignInterface $campaign)
    {
        $scenarioAOptimizerIds = $campaign->getScenarioAOptimizerIds();
        $scenarioBOptimizerIds = $campaign->getScenarioBOptimizerIds();

        $intersect = array_intersect($scenarioAOptimizerIds, $scenarioBOptimizerIds);
        if ($intersect) {
            throw new ValidatorException(__("An optimizer can't be linked to both scenarios."));
        }

        if ($campaign->getScenarioAPercentage() < 0 || $campaign->getScenarioAPercentage() > 100) {
            throw new ValidatorException(__('The percentage for scenario A should be between 0 and 100.'));
        }
    }
}
