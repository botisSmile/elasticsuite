<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSearchResultsInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSearchResultsInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign as CampaignResource;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Collection as CampaignCollection;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;

/**
 * Class CampaignRepository
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignRepository implements CampaignRepositoryInterface
{
    /**
     * @var CampaignResource
     */
    private $resource;

    /**
     * @var CampaignFactory
     */
    private $factory;

    /**
     * @var CampaignCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CampaignSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Repository cache for campaigns, by ids
     *
     * @var CampaignInterface[]
     */
    private $campaignsById = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * CampaignRepository constructor.
     *
     * @param CampaignResource                      $resource             Resource model.
     * @param CampaignCollectionFactory             $collectionFactory    Collection factory.
     * @param CampaignFactory                       $factory              Model factory.
     * @param CollectionProcessorInterface          $collectionProcessor  Collection processor.
     * @param CampaignSearchResultsInterfaceFactory $searchResultsFactory Search results factory.
     * @param EntityManager                         $entityManager        Entity Manager.
     */
    public function __construct(
        CampaignResource $resource,
        CampaignCollectionFactory $collectionFactory,
        CampaignFactory $factory,
        CollectionProcessorInterface $collectionProcessor,
        CampaignSearchResultsInterfaceFactory $searchResultsFactory,
        EntityManager $entityManager
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException
     */
    public function save(CampaignInterface $item): CampaignInterface
    {
        try {
            $this->entityManager->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the campaign: %1',
                $exception->getMessage()
            ));
        }

        $this->campaignsById[$item->getId()] = $item;

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CampaignInterface $item): void
    {
        $this->resource->delete($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var CampaignCollection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var CampaignSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.StaticAccess) Mandatory to throw the exception via static access to be compliant
     * with Magento Extension Quality Program
     */
    public function getById(int $campaignId) : CampaignInterface
    {
        if (!isset($this->campaignsById[$campaignId])) {
            $item = $this->factory->create();
            $this->entityManager->load($item, $campaignId);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField(CampaignInterface::CAMPAIGN_ID, $campaignId);
            }

            $this->campaignsById[$campaignId] = $item;
        }

        return $this->campaignsById[$campaignId];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(int $campaignId): void
    {
        $this->delete($this->getById($campaignId));
    }
}
