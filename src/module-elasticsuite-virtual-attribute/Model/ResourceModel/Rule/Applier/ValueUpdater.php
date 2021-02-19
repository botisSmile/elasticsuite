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
     * @var integer
     */
    private $optionId;

    /**
     * @var integer
     */
    private $storeId;

    /**
     * @var string
     */
    private $updateTable;

    /**
     * ValueUpdater constructor.
     *
     * @param \Magento\Eav\Model\Entity\Context                                                  $context       Context
     * @param \Magento\Store\Model\StoreManagerInterface                                         $storeManager  Store Manager
     * @param \Magento\Catalog\Model\Factory                                                     $modelFactory  Model Factory
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy $tableStrategy Table Strategy
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface                                $attribute     Attribute
     * @param int                                                                                $optionId      Option Id
     * @param int                                                                                $storeId       Store Id
     * @param array                                                                              $data          Data
     */
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
        $this->updateTable   = $this->tableStrategy->getTemporaryTableName($this->attribute);
    }

    /**
     * Remove attribute value from existing data row.
     *
     * @param array $row The row containing product data
     */
    public function removeValue($row)
    {
        $attribute = $this->attribute;

        // New value is old value without the value to remove.
        $newValue = array_diff(explode(',', $row[$this->attribute->getAttributeCode()]), [$this->optionId]);

        // Register new value to save if not empty.
        $this->saveAttributeValue($attribute, $row[$this->getLinkField()], $newValue);
    }

    /**
     * Add attribute value to existing data row.
     *
     * @param array $row The row containing current product data
     */
    public function updateValue($row)
    {
        $attribute     = $this->attribute;
        $frontendInput = $attribute->getFrontendInput();
        $newValue      = [$this->optionId]; // New value default to the option_id of the rule. It will replace old value for select.

        // If attribute is multiselect and already has value, append new value to existing.
        if ($frontendInput === 'multiselect') {
            $oldValue = $row[$attribute->getAttributeCode()];
            if ((string) $oldValue !== '') {
                $newValue = array_unique(array_merge(explode(',', $oldValue), [$this->optionId]));
            }
        }

        $this->saveAttributeValue($attribute, $row[$this->getLinkField()], $newValue);
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
            $connection->insertOnDuplicate($table, $data, ['value']);
        }

        if ($this->_storeManager->hasSingleStore()) {
            $storeId = $this->getDefaultStoreId();
            $connection->delete(
                $this->getUpdateTable(),
                [
                    'attribute_id = ?' => $this->attribute->getAttributeId(),
                    'store_id <> ?'    => (int) $storeId,
                ]
            );
        }

        // Reset data arrays.
        $this->_attributeValuesToSave = [];

        return $this;
    }

    /**
     * Unset all rows of temporary table belonging to current option Id.
     * Called when cleaning up data linked to a disabled rule.
     */
    public function removeOptionId()
    {
        $this->getConnection()->update(
            $this->getUpdateTable(),
            ['value' => null],
            ['value = ?' => $this->optionId]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * {@inheritdoc}
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $storeId  = $this->storeId;
        $table    = $this->getUpdateTable();
        $entityId = $object->getId();

        $data = new \Magento\Framework\DataObject(
            [
                'attribute_id'        => $attribute->getAttributeId(),
                'store_id'            => $storeId,
                $this->getLinkField() => $entityId,
                'value'               => $this->_prepareValueForSave($value, $attribute),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $table);

        if ($attribute->isScopeStore()) {
            // Update attribute value for store.
            $this->_attributeValuesToSave[$table][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            // Update attribute value for website.
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int) $storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            // Update global attribute value.
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

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
     * Retrieve Update table name (a temporary table based on the current attribute).
     *
     * @return string
     */
    private function getUpdateTable()
    {
        return $this->updateTable;
    }
}
