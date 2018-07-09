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
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule
     */
    private $resource;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    private $collectionFactory;

    /**
     * RuleService constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $collectionFactory Collection Factory.
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule                   $resource          Rule Resource Model.
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource          = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(array $ruleIds)
    {
        $this->resource->refreshRulesByIds($ruleIds);
    }
}
