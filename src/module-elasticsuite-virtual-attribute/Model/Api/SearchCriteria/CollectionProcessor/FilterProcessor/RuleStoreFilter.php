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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

/**
 * Apply store filter on a rule collection.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RuleStoreFilter implements \Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(\Magento\Framework\Api\Filter $filter, \Magento\Framework\Data\Collection\AbstractDb $collection)
    {
        /** @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Collection $collection */
        $collection->addStoreFilter($filter->getValue());

        return true;
    }
}
