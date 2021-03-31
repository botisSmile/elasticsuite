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
 * Campaign abstract date validator.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
abstract class AbstractDate implements ValidatorInterface
{
    /**
     * @var DateFilter
     */
    protected $dateFilter;

    /**
     * Date validator constructor.
     *
     * @param DateFilter $dateFilter Date filter
     */
    public function __construct(DateFilter $dateFilter)
    {
        $this->dateFilter = $dateFilter;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(CampaignInterface $campaign)
    {
        $startDate = $campaign->getStartDate();
        $endDate   = $campaign->getEndDate();
        if (!$startDate || !$endDate) {
            throw new ValidatorException(__('Start and end date are mandatory.'));
        }
    }

    /**
     * Filter date.
     *
     * @param mixed $date Date
     * @return string
     * @throws Exception
     */
    protected function filterDate($date)
    {
        return $this->dateFilter->filter($date);
    }
}
