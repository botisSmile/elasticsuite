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
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
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
     * @param int         $campaignId   The campaign id
     * @param string|null $scenarioType Scenario type
     * @return array
     * @throws LocalizedException
     */
    public function getOptimizerIdsByCampaign(int $campaignId, string $scenarioType = null): array
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

        if ($scenarioType) {
            $select->where($this->getConnection()->quoteInto(
                CampaignOptimizerInterface::SCENARIO_TYPE . " = ?",
                $scenarioType
            ));
        }

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Extract optimizer ids linked to campaign filter (optionally) by campaign status and dates.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param array $optimizerIds           Array of optimizer ids
     * @param array $filterByCampaignStatus Filter by campaign status ?
     * @param bool  $filterByCampaignDates  Filter by campaign dates ?
     * @return array
     */
    public function extractOptimizerIdsLinkedToCampaign(
        array $optimizerIds,
        array $filterByCampaignStatus = [],
        bool $filterByCampaignDates = false
    ): array {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['campaign_optimizer' => $this->getMainTable()],
                [CampaignOptimizerInterface::OPTIMIZER_ID]
            )
            ->join(
                ['campaign' => $this->getTable(CampaignInterface::TABLE_NAME)],
                'campaign_optimizer.' . CampaignOptimizerInterface::CAMPAIGN_ID
                . ' = campaign.' . CampaignInterface::CAMPAIGN_ID,
                []
            )
            ->where('campaign_optimizer.' . CampaignOptimizerInterface::OPTIMIZER_ID . ' in (?)', $optimizerIds);

        if ($filterByCampaignStatus) {
            $select->where('campaign.' . CampaignInterface::STATUS . ' in (?)', $filterByCampaignStatus);
        }

        if ($filterByCampaignDates) {
            $select->where('campaign.' . CampaignInterface::START_DATE . ' >= CURDATE()');
            $select->where('campaign.' . CampaignInterface::END_DATE . ' <= CURDATE()');
        }

        return $connection->fetchCol($select);
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
     * Join campaign table to optimizer collection.
     *
     * @param OptimizerCollection $optimizerCollection Optimizer collection
     * @return OptimizerCollection
     */
    public function joinCampaignToOptimizerCollection(OptimizerCollection $optimizerCollection): OptimizerCollection
    {
        if (!$optimizerCollection->hasFlag('campaign_optimizer')) {
            $optimizerCollection->setFlag('campaign_optimizer', true);
            $optimizerCollection->getSelect()
                ->joinLeft(
                    ['campaign_optimizer' => $optimizerCollection->getTable(CampaignOptimizerInterface::TABLE_NAME)],
                    'main_table.optimizer_id = campaign_optimizer.optimizer_id',
                    []
                )
                ->joinLeft(
                    ['campaign' => $optimizerCollection->getTable(CampaignInterface::TABLE_NAME)],
                    'campaign.campaign_id = campaign_optimizer.campaign_id',
                    []
                )
            ;
        }

        return $optimizerCollection;
    }

    /**
     * Save campaign optimizer link.
     *
     * @param int    $campaignId   Campaign id
     * @param int    $optimizerId  Optimizer id
     * @param string $scenarioType Scenario type
     * @return void
     */
    public function saveCampaignOptimizerLink(int $campaignId, int $optimizerId, string $scenarioType): void
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getConnection()->getTableName(CampaignOptimizerInterface::TABLE_NAME),
            [
                CampaignOptimizerInterface::CAMPAIGN_ID => $campaignId,
                CampaignOptimizerInterface::OPTIMIZER_ID => $optimizerId,
                CampaignOptimizerInterface::SCENARIO_TYPE => $scenarioType,
            ],
            [
                CampaignOptimizerInterface::CAMPAIGN_ID,
                CampaignOptimizerInterface::OPTIMIZER_ID,
                CampaignOptimizerInterface::SCENARIO_TYPE,
            ]
        );
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
