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

namespace Smile\ElasticsuiteAbCampaign\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign as CampaignModel;

/**
 * Campaign Resource
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Campaign extends AbstractDb
{
    /**
     * Saves campaign data.
     *
     * @param CampaignInterface $campaign Campaign.
     *
     * @return Campaign
     * @throws CouldNotSaveException
     */
    public function saveCampaignData(CampaignInterface $campaign)
    {
        /** @var CampaignModel $campaign */
        if ($campaign->hasData()) {
            try {
                $campaignData = $this->_prepareDataForSave($campaign);
                /** @var Mysql $connection */
                $connection = $this->getConnection();
                $connection->insertArray(
                    $this->getMainTable(),
                    array_keys($campaignData),
                    [array_values($campaignData)],
                    AdapterInterface::INSERT_IGNORE
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There was an error while saving campaign.'));
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(CampaignInterface::TABLE_NAME, CampaignInterface::CAMPAIGN_ID);
    }
}
