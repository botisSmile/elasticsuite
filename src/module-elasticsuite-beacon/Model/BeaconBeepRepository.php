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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBeacon\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Smile\ElasticsuiteBeacon\Api\BeaconBeepRepositoryInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepSearchResultsInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepSearchResultsInterfaceFactory;
use Smile\ElasticsuiteBeacon\Model\BeaconBeepFactory;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBeep as BeaconBeepResource;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBeep\Collection as BeaconBeepCollection;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBeep\CollectionFactory as BeaconBeepCollectionFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class BeaconBeepRepository
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BeaconBeepRepository implements BeaconBeepRepositoryInterface
{
    /**
     * @var BeaconBeepResource
     */
    private $resource;

    /**
     * @var BeaconBeepFactory
     */
    private $factory;

    /**
     * @var BeaconBeepCollectionFactory
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
     * @var BeaconBeepSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * Repository cache for beacon Beeps, by ids
     *
     * @var BeaconBeepInterface[]
     */
    private $beaconBeepsById = [];

    /**
     * @var TimezoneInterface
     */
    private $datetime;

    /**
     * BeaconBeepRepository constructor.
     *
     * @param BeaconBeepResource                      $resource             Resource model.
     * @param BeaconBeepCollectionFactory             $collectionFactory    Collection factory.
     * @param BeaconBeepFactory                       $factory              Model factory.
     * @param JoinProcessorInterface                  $joinProcessor        Collection join processor.
     * @param CollectionProcessorInterface            $collectionProcessor  Collection processor.
     * @param BeaconBeepSearchResultsInterfaceFactory $searchResultsFactory Search results factory.
     * @param TimezoneInterface                       $datetime             Datetime.
     */
    public function __construct(
        BeaconBeepResource $resource,
        BeaconBeepCollectionFactory $collectionFactory,
        BeaconBeepFactory $factory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        BeaconBeepSearchResultsInterfaceFactory $searchResultsFactory,
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
    public function save(BeaconBeepInterface $item): BeaconBeepInterface
    {
        $this->resource->saveBeepData($item);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BeaconBeepInterface $item): void
    {
        $this->resource->delete($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : BeaconBeepSearchResultsInterface
    {
        /** @var BeaconBeepCollection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process(
            $collection,
            BeaconBeepInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var BeaconBeepSearchResultsInterface $searchResults */
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
    public function getById(int $id) : BeaconBeepInterface
    {
        if (!isset($this->beaconBeepsById[$id])) {
            $item = $this->factory->create();
            $this->resource->load($item, $id);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField(BeaconBeepInterface::BEEP_ID, $id);
            }
        }

        return $this->beaconBeepsById[$id];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteById(int $id): void
    {
        $this->delete($this->getById($id));
    }

    /**
     * {@inheritdoc}
     */
    public function alreadyExists(BeaconBeepInterface $item): bool
    {
        if ($item->getId()) {
            return true;
        }

        /** @var BeaconBeepCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(BeaconBeepInterface::CLIENT_ID, $item->getClientId())
            ->addFieldToFilter(BeaconBeepInterface::HOST_ID, $item->getHostId())
            ->addFieldToFilter(
                BeaconBeepInterface::CREATED_AT_DATE,
                $this->datetime->date($item->getCreatedAtDate())
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format(DateTime::DATETIME_PHP_FORMAT)
            )
            ->setPageSize(1);

        return ($collection->getSize() > 0);
    }
}
