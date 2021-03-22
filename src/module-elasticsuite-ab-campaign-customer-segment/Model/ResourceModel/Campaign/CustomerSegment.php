<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Model\ResourceModel\Campaign;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaignCustomerSegment\Api\Data\CampaignCustomerSegmentInterface;

/**
 * Campaign Customer Segment Resource Model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CustomerSegment extends AbstractDb
{
    /**
     * Retrieve all customer segments associated to a given campaign.
     *
     * @param CampaignInterface $campaign The campaign
     * @return array
     */
    public function getSegmentIdsByCampaign(CampaignInterface $campaign)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), CampaignCustomerSegmentInterface::SEGMENT_ID)
            ->where($this->getConnection()->quoteInto(CampaignInterface::CAMPAIGN_ID . " = ?", (int) $campaign->getId()));

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Save customer segment data for a given campaign.
     *
     * @param CampaignInterface $campaign       The campaign.
     * @param array             $limitationData An array containing segment limitation data to save.
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveLimitation($campaign, $limitationData)
    {
        $rows        = [];
        $campaignId = (int) $campaign->getId();

        $this->getConnection()->delete(
            $this->getMainTable(),
            $this->getConnection()->quoteInto(CampaignCustomerSegmentInterface::CAMPAIGN_ID . " = ?", $campaignId)
        );

        $fields = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($limitationData as $item) {
            $item[$this->getIdFieldName()] = $campaignId;
            $rows[] = array_replace(array_fill_keys(array_keys($fields), null), array_intersect_key($item, $fields));
        }

        $result = true;
        if (!empty($rows)) {
            $result = (bool) $this->getConnection()->insertArray($this->getMainTable(), array_keys($fields), $rows);
        }

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(CampaignCustomerSegmentInterface::TABLE_NAME, CampaignCustomerSegmentInterface::CAMPAIGN_ID);
    }
}
