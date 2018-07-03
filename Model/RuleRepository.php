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

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * PHP Constructor
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory              $ruleFactory           Rule Factory.
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory Rule Collection Factory.
     * @param \Magento\Framework\EntityManager\EntityManager                                 $entityManager         Entity Manager.
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface             $collectionProcessor   Collection Processor.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleSearchResultsInterfaceFactory $searchResultsFactory  Search Results Factory.
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory $ruleFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->ruleFactory           = $ruleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->entityManager         = $entityManager;
        $this->collectionProcessor   = $collectionProcessor;
        $this->searchResultsFactory  = $searchResultsFactory;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getById($ruleId)
    {
        if (!isset($this->ruleRepositoryById[$ruleId])) {
            $ruleModel = $this->ruleFactory->create();
            $rule      = $this->entityManager->load($ruleModel, $ruleId);
            if (!$rule->getId()) {
                throw new NoSuchEntityException(__('Rule with id "%1" does not exist.', $ruleId));
            }

            $this->ruleRepositoryById[$ruleId] = $rule;
        }

        return $this->ruleRepositoryById[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Collection $collection */
        $collection = $this->ruleCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule)
    {
        try {
            $this->entityManager->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the rule: %1', $exception->getMessage()));
        }

        $this->ruleRepositoryById[$rule->getId()] = $rule;

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule)
    {
        $ruleId = $rule->getId();

        $this->entityManager->delete($rule);

        if (isset($this->ruleRepositoryById[$ruleId])) {
            unset($this->ruleRepositoryById[$ruleId]);
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        return $this->delete($this->getById($ruleId));
    }
}
