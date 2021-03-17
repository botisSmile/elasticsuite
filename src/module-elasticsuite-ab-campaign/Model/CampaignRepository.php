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
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSearchResultsInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSearchResultsInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Model\CampaignFactory;
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
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

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
     * @var TimezoneInterface
     */
    private $datetime;

    /**
     * CampaignRepository constructor.
     *
     * @param CampaignResource                      $resource             Resource model.
     * @param CampaignCollectionFactory             $collectionFactory    Collection factory.
     * @param CampaignFactory                       $factory              Model factory.
     * @param JoinProcessorInterface                $joinProcessor        Collection join processor.
     * @param CollectionProcessorInterface          $collectionProcessor  Collection processor.
     * @param CampaignSearchResultsInterfaceFactory $searchResultsFactory Search results factory.
     * @param TimezoneInterface                     $datetime             Datetime.
     */
    public function __construct(
        CampaignResource $resource,
        CampaignCollectionFactory $collectionFactory,
        CampaignFactory $factory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        CampaignSearchResultsInterfaceFactory $searchResultsFactory,
        TimezoneInterface $datetime
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->datetime = $datetime;
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException
     */
    public function save(CampaignInterface $item): CampaignInterface
    {
        $this->resource->saveCampaignData($item);

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
    public function getList(SearchCriteriaInterface $searchCriteria) : CampaignSearchResultsInterface
    {
        /** @var CampaignCollection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process(
            $collection,
            CampaignInterface::class
        );

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
            $this->resource->load($item, $campaignId);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField(CampaignInterface::CAMPAIGN_ID, $campaignId);
            }
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
