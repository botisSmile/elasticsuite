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
use \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Elastic Suite Virtual Attribute Rule applier.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Applier
{
    /**
     * Applier constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier $resource              Applier Resource
     * @param RuleCollectionFactory                                                $ruleCollectionFactory Rule Collection Factory.
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier $resource,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ){
        $this->resource              = $resource;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Apply all rules building values for a given attribute Id.
     * They are applied together to take priority into account.
     *
     * @param int $attributeId The attribute Id
     *
     * @throws \Exception
     */
    public function applyByAttributeId($attributeId)
    {
        $rulesCollection = $this->ruleCollectionFactory->create();
        $rulesCollection->addFieldToFilter(RuleInterface::ATTRIBUTE_ID, $attributeId)
            ->addFieldToFilter(RuleInterface::IS_ACTIVE, (int) true)
            ->setOrder(RuleInterface::PRIORITY); // DESC is default sort.

        $this->resource->applyByAttributeId($attributeId, $rulesCollection);
    }
}
