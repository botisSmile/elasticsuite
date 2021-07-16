<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Api\CampaignSessionRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionSearchResultsInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionSearchResultsInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionInterface;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession as CampaignSessionResource;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession\Collection as CampaignSessionCollection;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession\CollectionFactory as CampaignSessionCollectionFactory;

/**
 * Class CampaignRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class CampaignSessionRepository implements CampaignSessionRepositoryInterface
{
    /**
     * @var CampaignSessionResource
     */
    private $resource;

    /**
     * @var CampaignSessionFactory
     */
    private $factory;

    /**
     * @var CampaignSessionCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CampaignSessionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Repository cache for campaign sessions, by ids
     *
     * @var CampaignSessionInterface[]
     */
    private $campaignSessionsById = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * CampaignSessionRepository constructor.
     *
     * @param CampaignSessionResource                      $resource              Resource
     * @param CampaignSessionCollectionFactory             $collectionFactory     CollectionFactory
     * @param CampaignSessionFactory                       $factory               Factory
     * @param CollectionProcessorInterface                 $collectionProcessor   Collection processor
     * @param CampaignSessionSearchResultsInterfaceFactory $searchResultsFactory  Search results factory
     * @param EntityManager                                $entityManager         Entity manager
     * @param SearchCriteriaBuilder                        $searchCriteriaBuilder Search criteria builder
     */
    public function __construct(
        CampaignSessionResource $resource,
        CampaignSessionCollectionFactory $collectionFactory,
        CampaignSessionFactory $factory,
        CollectionProcessorInterface $collectionProcessor,
        CampaignSessionSearchResultsInterfaceFactory $searchResultsFactory,
        EntityManager $entityManager,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->entityManager = $entityManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException
     */
    public function save(CampaignSessionInterface $item): CampaignSessionInterface
    {
        try {
            $this->entityManager->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the campaign session: %1',
                $exception->getMessage()
            ));
        }

        $this->campaignSessionsById[$item->getId()] = $item;

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CampaignSessionInterface $item): void
    {
        $this->resource->delete($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var CampaignSessionCollection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var CampaignSessionSearchResultsInterface $searchResults */
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
    public function getById(int $campaignSessionId) : CampaignSessionInterface
    {
        if (!isset($this->campaignSessionsById[$campaignSessionId])) {
            $item = $this->factory->create();
            $this->entityManager->load($item, $campaignSessionId);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField(
                    CampaignSessionInterface::CAMPAIGN_SESSION_ID,
                    $campaignSessionId
                );
            }
            $this->campaignSessionsById[$campaignSessionId] = $item;
        }

        return $this->campaignSessionsById[$campaignSessionId];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.StaticAccess) Mandatory to throw the exception via static access to be compliant
     * with Magento Extension Quality Program
     */
    public function getByCampaignSessionData(int $campaignId) : ?CampaignSessionInterface
    {
        /** @var CampaignSession|null $campaignSession */
        $campaignSession = null;

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CampaignSessionInterface::CAMPAIGN_ID, $campaignId)
            ->create();

        $campaignSessions = $this->getList($searchCriteria)->getItems();
        foreach ($campaignSessions as $item) {
            $campaignSession = $item;
            break;
        }

        if ($campaignSession !== null && !isset($this->campaignSessionsById[$campaignSession->getId()])) {
            $this->campaignSessionsById[$campaignSession->getId()] = $campaignSession;
        }

        return $campaignSession;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(int $campaignSessionId): void
    {
        $this->delete($this->getById($campaignSessionId));
    }
}
