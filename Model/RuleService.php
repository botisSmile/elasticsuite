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
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier
     */
    private $applier;

    /**
     * RuleService constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface                $ruleRepository        Rule Repository
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule                   $resource              Rule Resource
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory Rule Collection Factory
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier           $applier               Rules applier
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface $ruleRepository,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule $resource,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier $applier
    ) {
        $this->ruleRepository             = $ruleRepository;
        $this->resource                   = $resource;
        $this->ruleCollectionFactory      = $ruleCollectionFactory;
        $this->applier                    = $applier;
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
    public function applyAll()
    {
        $rulesCollection = $this->ruleCollectionFactory->create();
        $rulesCollection->addFieldToFilter(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::TO_REFRESH, (int) true)
            ->addFieldToFilter(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::IS_ACTIVE, (int) true);

        // Get all attribute Ids concerned by rules to refresh.
        // Refresh all rules linked to these attribute ids to ensure priority is properly managed.
        $attributeIds = $rulesCollection->getAllAttributeIds();

        foreach ($attributeIds as $attributeId) {
            $rulesCollection = $this->ruleCollectionFactory->create();
            $rulesCollection->addFieldToFilter(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::ATTRIBUTE_ID, $attributeId)
                ->addFieldToFilter(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::IS_ACTIVE, (int) true)
                ->setOrder(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::PRIORITY); // DESC is default sort.

            foreach ($rulesCollection as $rule) {
                $this->applier->removeValue($rule);
            }

            $this->applier->applyList($rulesCollection);

            foreach ($rulesCollection as $rule) {
                $rule->setToRefresh(false);
                $this->ruleRepository->save($rule);
            }
        }
    }
}
