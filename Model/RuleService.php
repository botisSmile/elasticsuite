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
namespace Smile\ElasticsuiteVirtualAttribute\Model;

use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Implementation of Rule Service.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RuleService implements \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule
     */
    private $resource;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    private $sqlBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $productAction;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * RuleService constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface          $ruleRepository             Rule Repository
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule             $resource                   Rule Resource
     * @param RuleCollectionFactory                                                    $ruleCollectionFactory      Rule Collection Factory
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder     $sqlBuilder                 SQL Builder
     * @param ProductCollectionFactory                                                 $productCollectionFactory   Product Collection Factory
     * @param \Magento\Catalog\Model\Product\Action                                    $productAction              Product Mass Action
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory Attribute Collection Factory
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface $ruleRepository,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule $resource,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder $sqlBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
    ) {
        $this->ruleRepository             = $ruleRepository;
        $this->resource                   = $resource;
        $this->ruleCollectionFactory      = $ruleCollectionFactory;
        $this->productCollectionFactory   = $productCollectionFactory;
        $this->sqlBuilder                 = $sqlBuilder;
        $this->productAction              = $productAction;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(array $ruleIds)
    {
        $this->resource->refreshRulesByIds($ruleIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingProductIds(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule, int $storeId)
    {
        $key = sprintf('%s_%s', $rule->getId(), $storeId);

        if (!isset($this->productIds[$key])) {
            $this->productIds[$key] = [];

            if (in_array($storeId, $rule->getStores()) || in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $rule->getStores())) {
                $collection = $this->productCollectionFactory->create();
                $collection->addStoreFilter($storeId);

                $conditions = $rule->getCondition()->getConditions();
                $conditions->collectValidatedAttributes($collection);

                // Filter on matched attribute_set_id of the rule "attribute_id".
                $attributeSetIds = $this->getAttributeSetIds($rule->getAttributeId());
                $collection->addFieldToFilter('attribute_set_id', ['in' => $attributeSetIds]);

                $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

                $collection->distinct(true);

                $this->productIds[$key] = $collection->getAllIds();
            }
        }

        return $this->productIds[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function applyAll()
    {
        $rulesCollection = $this->ruleCollectionFactory->create();
        $rulesCollection->addFieldToFilter(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::TO_REFRESH, (int) true)
                        ->setOrder(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::PRIORITY);

        foreach ($rulesCollection as $rule) {
            foreach ($rule->getStores() as $storeId) {
                $this->applyRule($rule, $storeId);
            }
            $rule->setToRefresh(false);
            $this->ruleRepository->save($rule);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyRule(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule, int $storeId)
    {
        $productIds  = $this->getMatchingProductIds($rule, $storeId);
        $attributeId = $rule->getAttributeId();
        $optionId    = $rule->getOptionId();

        // Remove value for products that were having it previously.
        $oldProductsCollection = $this->productCollectionFactory->create();
        $attributeCode         = $oldProductsCollection->getEntity()->getAttribute($attributeId)->getAttributeCode();
        $attributeSetIds       = $this->getAttributeSetIds($rule->getAttributeId());

        $oldProductsCollection->addStoreFilter($storeId);
        $oldProductsCollection->addFieldToFilter('attribute_set_id', ['in' => $attributeSetIds])
                              ->addAttributeToFilter($attributeCode, $optionId);

        $oldProductIds = $oldProductsCollection->getAllIds();

        if (!empty($oldProductIds)) {
            // updateAttributes will trigger a reindex for catalogsearch_fulltext if indexer is not "on schedule".
            $this->productAction->updateAttributes($oldProductIds, [$attributeCode => null], $storeId);
        }

        // Update matching products.
        if (!empty($productIds)) {
            // updateAttributes will trigger a reindex for catalogsearch_fulltext if indexer is not "on schedule".
            $this->productAction->updateAttributes($productIds, [$attributeCode => $optionId], $storeId);
        }
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
        $collection = $this->attributeCollectionFactory->create();
        $collection->addSetInfo(true);
        $collection->getSelect()->where('main_table.attribute_id = ?', $attributeId);

        $attribute = $collection->getFirstItem();

        $attributeSetIds = [];
        if ($attribute->getAttributeId() && $attribute->getAttributeSetInfo()) {
            $attributeSetIds = array_keys($attribute->getAttributeSetInfo());
        }

        return $attributeSetIds;
    }
}
