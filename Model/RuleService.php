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

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

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
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\ApplierList
     */
    private $applierList;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\PublisherFactory
     */
    private $publisherFactory;

    /**
     * RuleService constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface                $ruleRepository        Rule Repository
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule                   $resource              Rule Resource
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory Rule Collection Factory
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\ApplierList                     $applierList           Rules appliers
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\PublisherFactory  $publisherFactory      Rule  publisher Factory
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface $ruleRepository,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule $resource,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\ApplierList $applierList,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\PublisherFactory $publisherFactory
    ) {
        $this->ruleRepository        = $ruleRepository;
        $this->resource              = $resource;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->applierList           = $applierList;
        $this->publisherFactory      = $publisherFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleRefresh(array $ruleIds)
    {
        $this->resource->setToRefreshByIds($ruleIds);
    }

    /**
     * {@inheritdoc}
     */
    public function processRefresh()
    {
        // Get all attribute Ids concerned by rules to refresh.
        // Refresh all rules linked to these attribute ids to ensure priority is properly managed.
        $rulesCollection = $this->getRulesToRefresh();
        $attributeIds    = $rulesCollection->getAllAttributeIds();

        foreach ($attributeIds as $attributeId) {
            $appliers  = $this->applierList->get($attributeId);
            /** @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Publisher $publisher */
            $publisher = $this->publisherFactory->create(['attributeId' => $attributeId]);
            foreach ($appliers as $applier) {
                $applier->apply();
            }
            $publisher->publish();
        }

        /*
        foreach ($rulesCollection as $rule) {
            $rule->setToRefresh(false);
            $this->ruleRepository->save($rule);
        }*/
    }

    /**
     * Retrieve rules flagged as scheduled for refreshment.
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Collection
     */
    private function getRulesToRefresh()
    {
        $rulesCollection = $this->ruleCollectionFactory->create();
        $rulesCollection->addFieldToFilter(RuleInterface::TO_REFRESH, (int) true);

        return $rulesCollection;
    }
}
