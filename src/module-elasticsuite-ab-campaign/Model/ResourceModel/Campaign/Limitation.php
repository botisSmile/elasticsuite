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

namespace Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Campaign Limitation Resource
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Limitation extends AbstractDb
{
    /**
     * Retrieve all categories associated to a given campaign.
     *
     * @param CampaignInterface $campaign The campaign
     * @return array
     */
    public function getCategoryIdsByCampaign(CampaignInterface $campaign)
    {
        return $this->getLimitationData($campaign, 'category_id');
    }

    /**
     * Retrieve all search queries associated to a given campaign.
     *
     * @param CampaignInterface $campaign The campaign
     * @return array
     */
    public function getQueryIdsByCampaign(CampaignInterface $campaign)
    {
        return $this->getLimitationData($campaign, 'query_id');
    }

    /**
     * Save limitation data for a given campaign.
     *
     * @param CampaignInterface $campaign       The campaign.
     * @param array             $limitationData An array containing limitation data to save.
     * @return bool
     * @throws LocalizedException
     */
    public function saveLimitation(CampaignInterface $campaign, array $limitationData): bool
    {
        $rows        = [];
        $campaignId = (int) $campaign->getId();

        $this->getConnection()->delete(
            $this->getMainTable(),
            $this->getConnection()->quoteInto(CampaignInterface::CAMPAIGN_ID . " = ?", $campaignId)
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
        $this->_init(CampaignInterface::TABLE_NAME_LIMITATION, CampaignInterface::CAMPAIGN_ID);
    }

    /**
     * Get Limitation data for a given campaign.
     *
     * @param CampaignInterface $campaign The campaign
     * @param string            $column   The column to fetch
     * @return array
     */
    private function getLimitationData(CampaignInterface $campaign, $column)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), $column)
            ->where($this->getConnection()->quoteInto(CampaignInterface::CAMPAIGN_ID . " = ?", (int) $campaign->getId()));

        return $this->getConnection()->fetchCol($select);
    }
}
