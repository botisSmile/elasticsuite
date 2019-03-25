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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Virtual Attribute Rule Product Matcher
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Matcher extends AbstractDb
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder
     */
    private $sqlBuilder;

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
     * @var \Magento\CatalogRule\Model\Rule
     */
    private $condition;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * Matcher constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context                        $context                    Context
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder     $sqlBuilder                 SQL Conditions Builder
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface                      $attribute                  Attribute
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory           $collectionFactory          Product Collection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory Attribute Collection
     * @param int                                                                      $optionId                   Option Id
     * @param int                                                                      $storeId                    Store Id
     * @param \Magento\CatalogRule\Model\Rule                                          $condition                  Condition
     * @param null                                                                     $connectionName             Connection Name
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder $sqlBuilder,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        int $optionId,
        int $storeId,
        \Magento\CatalogRule\Model\Rule $condition,
        $connectionName = null
    ) {
        $this->sqlBuilder                 = $sqlBuilder;
        $this->attribute                  = $attribute;
        $this->optionId                   = $optionId;
        $this->storeId                    = $storeId;
        $this->condition                  = $condition;
        $this->collectionFactory          = $collectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * Match and yield rows for current conditions.
     *
     * @return \Generator
     */
    public function matchByCondition()
    {
        $fetchStmt = $this->getFetchStmt($this->getMatchingProductCollection());

        do {
            // $row contains current product data :
            // [entity_id => 1234, attributeCode => previousValue, value_id => value_id]
            $row = $fetchStmt->fetch();

            if (!empty($row)) {
                yield $row;
            }
        } while (!empty($row));
    }

    /**
     * Match and yield rows for products already having the value.
     *
     * @return \Generator
     */
    public function matchByOptionId()
    {
        $fetchStmt = $this->getFetchStmt($this->getAppliedProductCollection());

        do {
            // $row contains current product data :
            // [entity_id => 1234, attributeCode => previousValue, value_id => value_id]
            $row = $fetchStmt->fetch();

            if (!empty($row)) {
                yield $row;
            }
        } while (!empty($row));
    }

    /**
     * Resource initialization
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->attribute->getBackendTable(), 'value_id');
    }

    /**
     * Init product collection filtered on current attribute and store.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($this->storeId);

        // Filter on matched attribute_set_id of the rule "attribute_id".
        $attributeSetIds = $this->getAttributeSetIds();
        $collection->addFieldToFilter('attribute_set_id', ['in' => $attributeSetIds]);

        $collection->distinct(true);

        return $collection;
    }

    /**
     * Get product collection matching current condition. (for update purposes).
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getMatchingProductCollection()
    {
        $collection = $this->getProductCollection();
        $conditions = $this->condition->getConditions();
        $conditions->collectValidatedAttributes($collection);

        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        return $collection;
    }

    /**
     * Get product collection already having current value applied. (for deletion purposes).
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getAppliedProductCollection()
    {
        $collection = $this->getProductCollection();

        $collection->addAttributeToFilter(
            $this->attribute->getAttributeCode(),
            ['finset' => $this->optionId] // Use 'finset' to match also multiselects.
        );

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
     *
     * @return \Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getFetchStmt(\Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection)
    {
        $attributeCode  = $this->attribute->getAttributeCode();
        $attributeTable = $this->getConnection()->getTableName(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::ATTRIBUTE_TABLE_ALIAS_PREFIX . $attributeCode
        );

        $entityIdField = $productCollection->getEntity()->getEntityIdField();
        $linkField     = $productCollection->getEntity()->getLinkField();
        $productCollection->addAttributeToSelect($attributeCode, 'left');

        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $productCollection->getSelect()->columns(
            [
                $entityIdField => 'e.' . $entityIdField,
                $linkField     => 'e.' . $linkField,
                $attributeCode => $attributeTable . '.value',
                'value_id'     => $attributeTable . '.value_id',
            ]
        );

        $select = $productCollection->getSelect();

        return $this->getConnection()->query($select);
    }

    /**
     * Get attribute set ids of current attribute.
     *
     * @return array
     */
    private function getAttributeSetIds()
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addSetInfo(true);
        $collection->getSelect()->where('main_table.attribute_id = ?', $this->attribute->getAttributeId());

        $attribute = $collection->getFirstItem();

        $attributeSetIds = [];
        if ($attribute->getAttributeId() && $attribute->getAttributeSetInfo()) {
            $attributeSetIds = array_keys($attribute->getAttributeSetInfo());
        }

        return $attributeSetIds;
    }
}
