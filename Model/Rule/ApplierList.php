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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Applier List for Rules.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ApplierList
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var array
     */
    private $appliers = [];

    /**
     * ApplierList constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\ApplierFactory                  $applierFactory        Applier Factory
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory Rule Factory
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface                       $attributeRepository   Attribute Repository
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\ApplierFactory $applierFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->applierFactory        = $applierFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->attributeRepository   = $attributeRepository;
    }

    /**
     * Get appliers properly ordered for a given attribute Id.
     *
     * @param int $attributeId The attribute Id
     *
     * @return Applier[]
     */
    public function get(int $attributeId)
    {
        $attribute = $this->getAttribute($attributeId);

        if (!isset($this->appliers[$attributeId])) {
            $this->appliers[$attributeId] = [];

            if ($attribute !== false) {
                $rulesCollection = $this->ruleCollectionFactory->create();

                $rulesCollection->addFieldToFilter(RuleInterface::ATTRIBUTE_ID, $attributeId);
                $rulesCollection
                    ->setOrder(RuleInterface::IS_ACTIVE) // Filter disabled rules at the end since they will only process remove.
                    ->setOrder(RuleInterface::PRIORITY); // DESC is default sort.

                /** @var RuleInterface $rule */
                foreach ($rulesCollection as $rule) {
                    if ($this->getAttribute($attributeId)) {
                        foreach ($rule->getStores() as $storeId) {
                            $this->appliers[$attributeId][] = $this->applierFactory->create(
                                [
                                    'attribute'  => $attribute,
                                    'rule'       => $rule,
                                    'optionId'   => $rule->getOptionId(),
                                    'condition'  => $rule->getCondition(),
                                    'ruleStatus' => $rule->isActive(),
                                    'storeId'    => (int) $storeId,
                                ]
                            );
                        }
                    }
                }
            }
        }

        return $this->appliers[$attributeId];
    }

    /**
     * Get attribute by Id.
     *
     * @param int $attributeId Attribute Id.
     *
     * @return bool|\Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private function getAttribute($attributeId)
    {
        try {
            $attribute = $this->attributeRepository->get($attributeId);
        } catch (\Exception $exception) {
            $attribute = false;
        }

        return $attribute;
    }
}
