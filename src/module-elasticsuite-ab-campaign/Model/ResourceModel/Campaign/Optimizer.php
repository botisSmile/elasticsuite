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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Zend_Db_Expr;

/**
 * Campaign Optimizer Resource
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Optimizer extends AbstractDb
{
    /**
     * Retrieve optimizer ids by campaign.
     *
     * @param int $campaignId The campaign id
     * @return array
     */
    public function getOptimizerIdsByCampaign(int $campaignId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(
                $this->getMainTable(),
                [
                    CampaignOptimizerInterface::SCENARIO_TYPE,
                    'optimizer_ids' => new Zend_Db_Expr("GROUP_CONCAT(optimizer_id SEPARATOR ',')"),
                ]
            )
            ->where($this->getConnection()->quoteInto(
                CampaignOptimizerInterface::CAMPAIGN_ID . " = ?",
                $campaignId
            ))
            ->group(CampaignOptimizerInterface::SCENARIO_TYPE)
        ;

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Save limitation data for a given campaign.
     *
     * @param int    $campaignId   Campaign id
     * @param array  $optimizerIds Optimizer ids
     * @param string $scenarioType Scenario type
     * @return void
     * @throws LocalizedException
     */
    public function saveCampaignOptimizer(int $campaignId, array $optimizerIds, string $scenarioType)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [
                CampaignOptimizerInterface::CAMPAIGN_ID . " = ?" => $campaignId,
                CampaignOptimizerInterface::SCENARIO_TYPE . " = ?" => $scenarioType,
            ]
        );

        $optimizerData = [];
        foreach ($optimizerIds as $optimizerId) {
            $optimizerData[] = [
                CampaignOptimizerInterface::CAMPAIGN_ID => $campaignId,
                CampaignOptimizerInterface::OPTIMIZER_ID => (int) $optimizerId,
                CampaignOptimizerInterface::SCENARIO_TYPE => $scenarioType,
            ];
        }

        if ($optimizerData) {
            $connection->insertArray(
                $connection->getTableName(CampaignOptimizerInterface::TABLE_NAME),
                [
                    CampaignOptimizerInterface::CAMPAIGN_ID,
                    CampaignOptimizerInterface::OPTIMIZER_ID,
                    CampaignOptimizerInterface::SCENARIO_TYPE,
                ],
                $optimizerData
            );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(CampaignOptimizerInterface::TABLE_NAME, CampaignOptimizerInterface::CAMPAIGN_ID);
    }
}
