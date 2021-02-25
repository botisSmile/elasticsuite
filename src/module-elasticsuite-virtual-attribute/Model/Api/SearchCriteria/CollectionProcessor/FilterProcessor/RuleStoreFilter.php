<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
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
