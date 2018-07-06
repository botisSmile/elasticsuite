<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
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
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Rule constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context        DB Context
     * @param \Magento\CatalogRule\Model\RuleFactory            $ruleFactory    Rule Factory
     * @param null                                              $connectionName Connection name
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        $connectionName = null
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $connectionName);
    }

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
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $rule          = $this->ruleFactory->create();
        $ruleCondition = $object->getCondition();

        if (is_object($ruleCondition)) {
            $rule = $ruleCondition;
        } elseif (is_array($ruleCondition)) {
            $rule->getConditions()->loadArray($ruleCondition);
        }

        $object->setCondition(serialize($rule->getConditions()->asArray()));

        return parent::_beforeSave($object);
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
