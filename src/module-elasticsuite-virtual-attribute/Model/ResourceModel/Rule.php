<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Smile Elastic Suite Virtual Attribute rule resource model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Persist relation between a given object and his rules.
     *
     * @param \Magento\Framework\Model\AbstractModel $object The rule
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveStoreRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->getStoreIds($object);
        $newStores = (array) $object->getStores();

        $table = $this->getTable(RuleInterface::STORE_TABLE_NAME);

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $this->getIdFieldName() . ' = ?' => (int) $object->getData($this->getIdFieldName()),
                'store_id IN (?)' => $delete,
            ];
            $this->getConnection()->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $this->getIdFieldName() => (int) $object->getData($this->getIdFieldName()),
                    'store_id'              => (int) $storeId,
                ];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return $object;
    }

    /**
     * Retrieve store ids associated to a given rule.
     *
     * @param \Magento\Framework\Model\AbstractModel $object The rule
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreIds(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['rs' => $this->getTable(RuleInterface::STORE_TABLE_NAME)], 'store_id')
            ->join(
                ['r' => $this->getMainTable()],
                'rs.' . $this->getIdFieldName() . ' = r.' . $this->getIdFieldName(),
                []
            )
            ->where('r.' . $this->getIdFieldName() . ' = :rule_id');

        return $connection->fetchCol($select, ['rule_id' => (int) $object->getId()]);
    }

    /**
     * Set rule Ids to be refreshed by ids.
     *
     * @param array $ruleIds The rule ids
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setToRefreshByIds(array $ruleIds)
    {
        $connection = $this->getConnection();

        if (!empty($ruleIds)) {
            $connection->update(
                $this->getMainTable(),
                [RuleInterface::TO_REFRESH => (int) true],
                $connection->prepareSqlCondition(RuleInterface::RULE_ID, ['in' => array_map('intval', $ruleIds)])
            );
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /* Using getter to force unserialize*/
        $object->getCondition();

        return parent::_afterLoad($object);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(RuleInterface::TABLE_NAME, RuleInterface::RULE_ID);
    }
}
