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

/**
 * Applier for Rules.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Applier extends \Magento\Catalog\Model\ResourceModel\Product\Action
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder
     */
    private $sqlBuilder;

    /**
     * Applier constructor.
     *
     * @param \Magento\Eav\Model\Entity\Context                                        $context                    Entity Context
     * @param \Magento\Store\Model\StoreManagerInterface                               $storeManager               Store Manager
     * @param \Magento\Catalog\Model\Factory                                           $modelFactory               Model Factory
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder     $sqlBuilder                 Rules Conditions SQL Builder
     * @param array                                                                    $data                       Data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder $sqlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $data);
        $this->sqlBuilder                 = $sqlBuilder;
    }

    /**
     * Apply a list of rules matching the same attribute_id.
     *
     * @param int                                                          $attributeId Attribute Id.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface[] $rules       List of rules.
     *
     * @throws \Exception
     */
    public function applyByAttributeId($attributeId, $rules)
    {
        $attribute = $this->getAttribute($attributeId);

        $this->createTemporaryTable($attribute);

        foreach ($rules as $rule) {
            $this->removeValue($rule);
        }

        foreach ($rules as $rule) {
            $this->registerUpdate($rule);
        }

        $this->getConnection()->beginTransaction();

        try {
            $sourceColumns = $targetColumns = [$this->getLinkField(), 'attribute_id', 'store_id'];

            $select = $this->getConnection()->select()->from($this->getTemporaryTableName($attribute), $sourceColumns);
            $query  = $this->getConnection()->insertFromSelect(
                $select,
                $attribute->getBackendTable(),
                $targetColumns,
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $this->getConnection()->query($query);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->dropTemporaryTable($attribute);
    }

    /**
     * Register all data update for a given rule into a temporary table.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule The rule
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function registerUpdate(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule)
    {
        $optionId = $rule->getOptionId();

        $attribute = $this->getAttributeForUpdate($rule->getAttributeId());
        $this->getConnection()->beginTransaction();
        try {
            foreach ($rule->getStores() as $storeId) {
                $productCollection = $this->getProductCollection($rule, $storeId);
                $fetchStmt         = $this->getFetchStmt($productCollection, $attribute);
                $cpt               = 0;

                do {
                    // $row contains current product data :
                    // [entity_id => 1234, attributeCode => previousValue, value_id => value_id]
                    $row = $fetchStmt->fetch();

                    $cpt++;
                    if (!empty($row)) {
                        $this->addAttributeValue($attribute, $row, $optionId, $storeId);
                    }

                    if ($cpt % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                } while (!empty($row));
            }

            $this->_processAttributeValues();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Remove all attributes values built by a rule.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule The rule
     *
     * @throws \Exception
     */
    public function removeValue(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule)
    {
        $attribute       = $this->getAttribute($rule->getAttributeId());
        $optionId        = $rule->getOptionId();
        $attributeCode   = $attribute->getAttributeCode();
        $attributeSetIds = $this->getAttributeSetIds($rule->getAttributeId());

        $this->getConnection()->beginTransaction();
        try {
            foreach ($rule->getStores() as $storeId) {
                /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productsCollection */
                $productsCollection = $this->_universalFactory->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
                $productsCollection->addStoreFilter($storeId)
                    ->addFieldToFilter('attribute_set_id', ['in' => $attributeSetIds])
                    ->addAttributeToFilter($attributeCode, ['finset' => $optionId]); // Use 'finset' to match also multiselects.

                $fetchStmt = $this->getFetchStmt($productsCollection, $attribute);
                $cpt       = 0;
                do {
                    $row = $fetchStmt->fetch();
                    $cpt++;

                    if (!empty($row)) {
                        $this->deleteAttributeValue($attribute, $row, $optionId, $storeId);
                    }

                    if ($cpt % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                } while (!empty($row));
            }

            $this->_processAttributeValues();
            $this->getConnection()->commit();

        } catch (\Exception $e) {
            $this->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * Add attribute value to existing data row.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     * @param array                                               $row       The row containing current product data
     * @param string                                              $value     The value to remove
     * @param int                                                 $storeId   The store Id
     */
    private function addAttributeValue($attribute, $row, $value, $storeId)
    {
        $frontendInput = $attribute->getFrontendInput();
        $newValue      = $value; // New value default to the option_id of the rule.

        // If attribute is multiselect and already has value, append new value after.
        if ($frontendInput === 'multiselect' && (strpos($row[$attribute->getAttributeCode()], ',') !== false)) {
            $newValue = $row[$attribute->getAttributeCode()] . ',' . trim($value);
        }

        // Register new value to save if not empty.
        $object = new \Magento\Framework\DataObject();
        $object->setStoreId($storeId);
        $object->setId($row[$this->getIdFieldName()]);
        $object->setEntityId($row[$this->getIdFieldName()]);

        $this->_saveAttributeValue($object, $attribute, $newValue);
    }

    /**
     * Remove attribute value from existing data row.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     * @param array                                               $row       The row containing product data
     * @param string                                              $value     The value to remove
     * @param int                                                 $storeId   The store Id
     */
    private function deleteAttributeValue($attribute, $row, $value, $storeId)
    {
        $table = $attribute->getBackendTable();

        // New value is old value without the value to remove.
        $newValue = array_diff(explode(',', $row[$attribute->getAttributeCode()]), [$value]);

        if (empty($newValue)) {
            // Delete row since new value would be empty.
            if (!isset($this->_attributeValuesToDelete[$table])) {
                $this->_attributeValuesToDelete[$table] = [];
            }
            $this->_attributeValuesToDelete[$table] = array_merge($this->_attributeValuesToDelete[$table], [$row['value_id']]);

            return;
        }

        // Register new value to save if not empty.
        $object = new \Magento\Framework\DataObject();
        $object->setStoreId($storeId);
        $object->setId($row[$this->getIdFieldName()]);
        $object->setEntityId($row[$this->getIdFieldName()]);

        $this->_saveAttributeValue($object, $attribute, implode(',', $newValue));
    }

    /**
     * Get product collection matching a rule.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule    The rule
     * @param int                                                        $storeId The store Id
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule, int $storeId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_universalFactory->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
        $collection->addStoreFilter($storeId);

        $conditions = $rule->getCondition()->getConditions();
        $conditions->collectValidatedAttributes($collection);

        // Filter on matched attribute_set_id of the rule "attribute_id".
        $attributeSetIds = $this->getAttributeSetIds($rule->getAttributeId());
        $collection->addFieldToFilter('attribute_set_id', ['in' => $attributeSetIds]);

        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        $collection->distinct(true);

        return $collection;
    }

    /**
     * Get fetch statement for a collection and a given attribute.
     * Return rows containing :
     *  - entity_id / row_id
     *  - attribute_code as value
     *  - value_id
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection Product Collection
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface     $attribute         Product Attribute
     *
     * @return \Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getFetchStmt(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
    ) {
        $attributeCode  = $attribute->getAttributeCode();
        $attributeTable = $this->getConnection()->getTableName(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::ATTRIBUTE_TABLE_ALIAS_PREFIX . $attributeCode
        );

        $productCollection->addAttributeToSelect($attributeCode, 'left');

        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $productCollection->getSelect()->columns('e.' . $productCollection->getEntity()->getIdFieldName());
        $productCollection->getSelect()->columns([$attributeCode => $attributeTable . '.value']);
        $productCollection->getSelect()->columns(['value_id' => $attributeTable . '.value_id']);

        $select = $productCollection->getSelect();

        return $productCollection->getConnection()->query($select);
    }

    /**
     * Get attribute set ids of a given attribute Id.
     *
     * @param int $attributeId The attribute Id.
     *
     * @return array
     */
    private function getAttributeSetIds($attributeId)
    {
        $collection = $this->_universalFactory->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
        $collection->addSetInfo(true);
        $collection->getSelect()->where('main_table.attribute_id = ?', $attributeId);

        $attribute = $collection->getFirstItem();

        $attributeSetIds = [];
        if ($attribute->getAttributeId() && $attribute->getAttributeSetInfo()) {
            $attributeSetIds = array_keys($attribute->getAttributeSetInfo());
        }

        return $attributeSetIds;
    }

    /**
     * Fetch a new instance of attribute with custom backend table to process update.
     *
     * @param int $attributeId The attribute Id
     *
     * @return \Magento\Framework\DataObject
     */
    private function getAttributeForUpdate($attributeId)
    {
        $attributeCollection = $this->_universalFactory->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
        $attributeCollection->addFieldToFilter('main_table.attribute_id', $attributeId);
        $attribute = $attributeCollection->getFirstItem();

        // To process insert in temporary table instead of real one.
        $attribute->setBackendTable($this->getTemporaryTableName($attribute));

        return $attribute;
    }

    /**
     * Create temporary table for a given attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     */
    private function createTemporaryTable(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $temporaryName = $this->getTemporaryTableName($attribute);

        // Drop the temporary table in case it already exists on this (persistent?) connection.
        $this->getConnection()->dropTemporaryTable($temporaryName);

        $this->getConnection()->createTemporaryTableLike(
            $this->getConnection()->getTableName($temporaryName),
            $this->getConnection()->getTableName($attribute->getBackendTable()),
            true
        );
    }

    /**
     * Drop temporary table used for a given product attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     */
    private function dropTemporaryTable(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $temporaryName = $this->getTemporaryTableName($attribute);
        $this->getConnection()->dropTemporaryTable($temporaryName);
    }

    /**
     * Get temporary table name for a given product attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     *
     * @return string
     */
    private function getTemporaryTableName(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        return 'elasticsuite_' . $attribute->getBackendTable() . '_tmp';
    }
}
