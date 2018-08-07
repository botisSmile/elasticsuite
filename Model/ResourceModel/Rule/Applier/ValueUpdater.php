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
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Virtual Attribute Rule Product Value Updater
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ValueUpdater extends \Magento\Catalog\Model\ResourceModel\Product\Action
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy
     */
    private $tableStrategy;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private $attribute;

    /**
     * @var int
     */
    private $optionId;

    /**
     * @var int
     */
    private $storeId;


    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy $tableStrategy,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        int $optionId,
        int $storeId,
        array $data = []
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $data);
        $this->tableStrategy = $tableStrategy;
        $this->attribute     = $attribute;
        $this->optionId      = $optionId;
        $this->storeId       = $storeId;
    }

    /**
     * Remove attribute value from existing data row.
     *
     * @param array $row The row containing product data
     */
    public function remove($row)
    {
        $attribute = $this->getAttributeForUpdate();
        $table     = $attribute->getBackendTable();

        // New value is old value without the value to remove.
        $newValue = array_diff(explode(',', $row[$this->attribute->getAttributeCode()]), [$this->optionId]);

        $this->_attributeValuesToDelete[$table] = $this->optionId;

        // Register new value to save if not empty.
        // $newValue = implode(',', $newValue);
        $this->saveAttributeValue($attribute, $row[$this->getIdFieldName()], $newValue);
    }

    /**
     * Add attribute value to existing data row.
     *
     * @param array $row The row containing current product data
     */
    public function update($row)
    {
        $attribute     = $this->getAttributeForUpdate();
        $frontendInput = $attribute->getFrontendInput();
        $newValue      = [$this->optionId]; // New value default to the option_id of the rule. It will replace old value for select.

        // If attribute is multiselect and already has value, append new value to existing.
        if ($frontendInput === 'multiselect') {
            $oldValue = $row[$attribute->getAttributeCode()];
            if ((string) $oldValue !== '') {
                $newValue = array_unique(array_merge(explode(',', $oldValue), [$this->optionId]));
            }
        }

        $this->saveAttributeValue($attribute, $row[$this->getIdFieldName()], $newValue);
    }

    /**
     * Persist computed attribute values.
     *
     * @return $this
     */
    public function persist()
    {
        $connection = $this->getConnection();
        foreach ($this->_attributeValuesToSave as $table => $data) {
            $connection->insertArray(
                $table,
                array_keys(current($data)),
                $data,
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
            );
        }

        foreach ($this->_attributeValuesToDelete as $table => $values) {
            $connection->delete($table, ['value IN (?)' => $values]);
        }

        // reset data arrays
        $this->_attributeValuesToSave = [];
        $this->_attributeValuesToDelete = [];

        return $this;
    }

    /**
     * Save a value for a given attribute and a tuple of row id (entity_id/row_id) and new value.
     *
     * @param ProductAttributeInterface $attribute Attribute
     * @param int                       $rowId     ID Field value
     * @param array                     $newValue  New Value to save
     */
    private function saveAttributeValue($attribute, $rowId, $newValue)
    {
        $object = new \Magento\Framework\DataObject();
        $object->setStoreId($this->storeId);
        $object->setId($rowId);
        $object->setEntityId($rowId);

        if (empty($newValue)) {
            $this->_saveAttributeValue($object, $attribute, null);
        }

        foreach ($newValue as $value) {
            $this->_saveAttributeValue($object, $attribute, $value);
        }
    }

    /**
     * Fetch a new instance of attribute with custom backend table to process update.
     *
     * @return \Magento\Framework\DataObject
     */
    private function getAttributeForUpdate()
    {
        $attributeCollection = $this->_universalFactory->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
        $attributeCollection->addFieldToFilter('main_table.attribute_id', $this->attribute->getAttributeId());
        $attribute = $attributeCollection->getFirstItem();

        // To process insert in temporary table instead of real one.
        $attribute->setBackendTable($this->tableStrategy->getTemporaryTableName($this->attribute));

        return $attribute;
    }
}
