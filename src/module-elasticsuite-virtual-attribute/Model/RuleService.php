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
    public function scheduleRefreshByAttributeSetIds($attributeSetIds)
    {
        $rulesCollection = $this->ruleCollectionFactory->create();
        $rulesCollection->addAttributeSetIdFilter($attributeSetIds);

        if ($rulesCollection->getSize() > 0) {
            $this->scheduleRefresh($rulesCollection->getAllIds());
        }
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
            $this->processByAttributeId($attributeId);
        }

        foreach ($rulesCollection as $rule) {
            $rule->setToRefresh(false);
            $this->ruleRepository->save($rule);
        }
    }

    /**
     * Compute and publish all rules for a given attribute Id.
     *
     * @param int $attributeId The attribute Id
     *
     * @throws \Exception
     */
    private function processByAttributeId($attributeId)
    {
        $appliers  = $this->applierList->get($attributeId);

        /** @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Publisher $publisher */
        $publisher = $this->publisherFactory->create(['attributeId' => $attributeId]);

        foreach ($appliers as $applier) {
            $applier->apply();
        }

        // Cleanup data belonging to disabled rules if needed. Done in two-step because each applier is not aware of others.
        foreach ($appliers as $applier) {
            $applier->cleanup();
        }

        $publisher->publish();
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
