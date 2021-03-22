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

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign;

use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;

/**
 * Campaign composite validator.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CompositeValidator
{
    /**
     * @var ValidatorInterface[]
     */
    protected $dataValidators = [];

    /**
     * Composite validator constructor.
     *
     * @param array $dataValidators Data validators
     */
    public function __construct($dataValidators = [])
    {
        $this->dataValidators = $dataValidators;
    }

    /**
     * Validate campaign data.
     *
     * @param CampaignInterface $campaign Campaign
     * @return void
     * @throws ValidatorException
     */
    public function validateData(CampaignInterface $campaign)
    {
        foreach ($this->dataValidators as $dataValidator) {
            $dataValidator->validate($campaign);
        }
    }
}
