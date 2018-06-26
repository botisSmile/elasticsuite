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
 * Smile Elastic Suite Virtual Attribute Rule repository.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RuleRepository implements \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface
{
    /**
     * Rule Factory
     *
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory
     */
    private $ruleFactory;

    /**
     * repository cache for rule, by ids
     *
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface[]
     */
    private $ruleRepositoryById = [];

    /**
     * Rule Collection Factory
     *
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * PHP Constructor
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory              $ruleFactory           Rule Factory.
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory Rule Collection Factory.
     * @param \Magento\Framework\EntityManager\EntityManager                                 $entityManager         Entity Manager.
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory $ruleFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\EntityManager\EntityManager $entityManager
    ) {
        $this->ruleFactory           = $ruleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->entityManager         = $entityManager;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getById($ruleId)
    {
        // TODO: Implement getById() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        // TODO: Implement getList() method.
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule)
    {
        // TODO: Implement save() method.
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule)
    {
        // TODO: Implement delete() method.
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        // TODO: Implement deleteById() method.
    }
}
