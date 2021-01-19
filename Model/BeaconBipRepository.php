<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBeacon
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeacon\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

use Smile\ElasticsuiteBeacon\Api\BeaconBipRepositoryInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipSearchResultsInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipSearchResultsInterfaceFactory;
use Smile\ElasticsuiteBeacon\Model\BeaconBipFactory;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBip as BeaconBipResource;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBip\Collection as BeaconBipCollection;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBip\CollectionFactory as BeaconBipCollectionFactory;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBip\Command\Save as BeaconBipSaveService;

/**
 * Class BeaconBipRepository
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class BeaconBipRepository implements BeaconBipRepositoryInterface
{
    /**
     * @var BeaconBipResource
     */
    private $resource;

    /**
     * @var BeaconBipSaveService
     */
    private $beaconBipSaveService;

    /**
     * @var BeaconBipFactory
     */
    private $factory;

    /**
     * @var BeaconBipCollectionFactory
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
     * @var BeaconBipSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Repository cache for beacon bips, by ids
     *
     * @var BeaconBipInterface[]
     */
    private $beaconBipsById = [];

    /**
     * BeaconBipRepository constructor.
     *
     * @param BeaconBipResource                      $resource             Resource model.
     * @param BeaconBipSaveService                   $saveService          Save service.
     * @param BeaconBipCollectionFactory             $collectionFactory    Collection factory.
     * @param BeaconBipFactory                       $factory              Model factory.
     * @param JoinProcessorInterface                 $joinProcessor        Collection join processor.
     * @param CollectionProcessorInterface           $collectionProcessor  Collection processor.
     * @param BeaconBipSearchResultsInterfaceFactory $searchResultsFactory Search results factory.
     */
    public function __construct(
        BeaconBipResource $resource,
        BeaconBipSaveService $saveService,
        BeaconBipCollectionFactory $collectionFactory,
        BeaconBipFactory $factory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        BeaconBipSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->beaconBipSaveService = $saveService;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException
     */
    public function save(BeaconBipInterface $item): BeaconBipInterface
    {
        // $this->beaconBipSaveService->execute($item);
        // $this->resource->saveBipData($item);
        $this->resource->saveBipDataSoft($item);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BeaconBipInterface $item): void
    {
        $this->resource->delete($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : BeaconBipSearchResultsInterface
    {
        /** @var BeaconBipCollection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process(
            $collection,
            BeaconBipInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var BeaconBipSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.StaticAccess) Mandatory to throw the exception via static access to be compliant
     * with Magento Extension Quality Program
     */
    public function getById(int $id) : BeaconBipInterface
    {
        if (!isset($this->beaconBipsById[$id])) {
            $item = $this->factory->create();
            $this->resource->load($item, $id);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField(BeaconBipInterface::BIP_ID, $id);
            }
        }

        return $this->beaconBipsById[$id];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteById(int $id): void
    {
        $this->delete($this->getById($id));
    }
}
