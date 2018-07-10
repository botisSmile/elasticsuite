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
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    private $sqlBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * RuleService constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface      $ruleRepository           Rule Repository
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule         $resource                 Rule Resource
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder $sqlBuilder               SQL Builder
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory       $productCollectionFactory Product Collection Factory
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface $ruleRepository,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule $resource,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql\Builder $sqlBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->ruleRepository           = $ruleRepository;
        $this->resource                 = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sqlBuilder               = $sqlBuilder;
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
    public function getMatchingProductIds(int $ruleId, int $storeId)
    {
        $key = sprintf('%s_%s', $ruleId, $storeId);

        if (!isset($this->productIds[$key])) {
            $this->productIds[$key] = [];
            $rule                   = $this->ruleRepository->getById($ruleId);

            if (in_array($storeId, $rule->getStores()) || in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $rule->getStores())) {
                $collection = $this->productCollectionFactory->create();
                $collection->addStoreFilter($storeId);

                $conditions = $rule->getCondition()->getConditions();
                $conditions->collectValidatedAttributes($collection);

                $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

                $collection->distinct(true);

                $this->productIds[$key] = $collection->getAllIds();
            }
        }

        return $this->productIds[$key];
    }
}
