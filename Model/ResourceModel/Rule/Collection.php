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
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Smile Elastic Suite Virtual Attribute Rule collection.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = RuleInterface::RULE_ID;

    /**
     * @var int[]
     */
    private $storeIds = [];

    /**
     * Get store ids applied to current collection.
     *
     * @return int[]
     */
    public function getStoreIds()
    {
        return $this->storeIds;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store The store
     *
     * @return $this
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        $this->storeIds = $store;
        $this->addFilter('store', ['in' => $store], 'public');

        return $this;
    }

    /**
     * Retrieve all distinct options ids of current collection.
     *
     * @return array
     */
    public function getAllOptionIds()
    {
        $optionIdsSelect = clone $this->getSelect();
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $optionIdsSelect->distinct(true)->columns(RuleInterface::OPTION_ID, 'main_table');

        return $this->getConnection()->fetchCol($optionIdsSelect, $this->_bindParams);
    }

    /**
     * Filter rule collection for a given attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     *
     * @return $this
     */
    public function addAttributeFilter(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        if ($attribute->getAttributeId()) {
            $this->addFieldToFilter(RuleInterface::ATTRIBUTE_ID, (int) $attribute->getAttributeId());
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(
            'Smile\ElasticsuiteVirtualAttribute\Model\Rule',
            'Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule'
        );

        /* @see self::_renderFiltersBefore() */
        $this->_map['fields']['store']        = RuleInterface::STORE_TABLE_NAME . '.store_id';
        $this->_map['fields']['attribute_id'] = 'main_table.attribute_id';
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $linkedIds = $this->getColumnValues(RuleInterface::RULE_ID);

        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select     = $connection->select()
                 ->from($this->getTable(RuleInterface::STORE_TABLE_NAME))
                 ->where(RuleInterface::RULE_ID . ' IN (?)', array_map('intval', $linkedIds));

            $result = $connection->fetchAll($select);

            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[RuleInterface::RULE_ID]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData(RuleInterface::RULE_ID);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }

                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                [RuleInterface::STORE_TABLE_NAME => $this->getTable(RuleInterface::STORE_TABLE_NAME)],
                sprintf('main_table.%s = %s.%s', RuleInterface::RULE_ID, RuleInterface::STORE_TABLE_NAME, RuleInterface::RULE_ID),
                []
            )->group(
                'main_table.' . RuleInterface::RULE_ID
            );
        }

        parent::_renderFiltersBefore();
    }
}
