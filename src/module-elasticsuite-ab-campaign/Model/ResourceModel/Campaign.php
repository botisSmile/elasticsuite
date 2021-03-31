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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

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
     * Retrieve Search Containers for a given campaign.
     *
     * @param int $campaignId The campaign Id
     * @return array
     */
    public function getSearchContainersFromCampaignId($campaignId)
    {
        $connection = $this->getConnection();

        $select = $connection->select();

        $select->from(
            $this->getTable(CampaignInterface::TABLE_NAME_SEARCH_CONTAINER),
            [CampaignInterface::SEARCH_CONTAINER, 'apply_to']
        )->where(CampaignInterface::CAMPAIGN_ID . ' = ?', (int) $campaignId);

        return $connection->fetchPairs($select);
    }

    /**
     * Update end date.
     *
     * @param string $newDate    New date
     * @param int    $campaignId Campaign id
     */
    public function updateEndDate(string $newDate, int $campaignId)
    {
        $connection = $this->getConnection();
        $connection->update(
            $connection->getTableName(CampaignInterface::TABLE_NAME),
            [CampaignInterface::END_DATE => $newDate],
            [CampaignInterface::CAMPAIGN_ID . ' = ?' => $campaignId]
        );
    }

    /**
     * Update campaign status.
     *
     * @param string $status     New status
     * @param int    $campaignId Campaign id
     */
    public function updateStatus(string $status, int $campaignId)
    {
        $connection = $this->getConnection();
        $connection->update(
            $connection->getTableName(CampaignInterface::TABLE_NAME),
            [CampaignInterface::STATUS => $status],
            [CampaignInterface::CAMPAIGN_ID . ' = ?' => $campaignId]
        );
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(CampaignInterface::TABLE_NAME, CampaignInterface::CAMPAIGN_ID);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);

        $this->saveSearchContainerRelation($object);

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        /** @var \Smile\ElasticsuiteAbCampaign\Model\Campaign $object */
        if ($object->getId()) {
            $searchContainers = $this->getSearchContainersFromCampaignId($object->getId());
            $object->setSearchContainers($searchContainers);
            if ($object->getScenarioAPercentage()) {
                $object->setScenarioBPercentage(100 - $object->getScenarioAPercentage());
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * Saves relation between optimizer and search container
     *
     * @param AbstractModel $object Optimizer to save
     *
     * @return void
     */
    private function saveSearchContainerRelation(AbstractModel $object)
    {
        $searchContainers = $object->getSearchContainer();

        if (is_array($searchContainers) && (count($searchContainers) > 0)) {
            $searchContainerLinks = [];
            $deleteCondition = CampaignInterface::CAMPAIGN_ID . " = " . $object->getId();

            foreach ($searchContainers as $searchContainer) {
                $searchContainerName = (string) $searchContainer;
                // Treat autocomplete apply_to like the quick search.
                if ($searchContainerName === 'catalog_product_autocomplete') {
                    $searchContainerName = 'quick_search_container';
                }
                $searchContainerData = $object->getData($searchContainerName);
                $applyTo = is_array($searchContainerData) ? ((bool) $searchContainerData['apply_to'] ?? false) : false;
                $searchContainerLinks[(string) $searchContainer] = [
                    CampaignInterface::CAMPAIGN_ID      => (int) $object->getId(),
                    CampaignInterface::SEARCH_CONTAINER => (string) $searchContainer,
                    'apply_to'                          => (int) $applyTo,
                ];
            }

            $this->getConnection()->delete(
                $this->getTable(CampaignInterface::TABLE_NAME_SEARCH_CONTAINER),
                $deleteCondition
            );
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(CampaignInterface::TABLE_NAME_SEARCH_CONTAINER),
                $searchContainerLinks
            );
        }
    }
}
