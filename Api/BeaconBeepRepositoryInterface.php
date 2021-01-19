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

namespace Smile\ElasticsuiteBeacon\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepSearchResultsInterface;

/**
 * Interface BeaconBeepRepositoryInterface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface BeaconBeepRepositoryInterface
{
    /**
     * Save beacon beep.
     *
     * @param BeaconBeepInterface $item Beacon beep.
     *
     * @return BeaconBeepInterface
     * @throws AlreadyExistsException
     */
    public function save(BeaconBeepInterface $item): BeaconBeepInterface;

    /**
     * Delete beacon beep.
     *
     * @param BeaconBeepInterface $item Beacon beep.
     *
     * @return void
     * @throws \Exception
     */
    public function delete(BeaconBeepInterface $item): void;

    /**
     * Get a list of beacon beeps.
     *
     * @param SearchCriteriaInterface $searchCriteria Search criteria.
     *
     * @return BeaconBeepSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): BeaconBeepSearchResultsInterface;

    /**
     * Get beacon beep by id.
     *
     * @param int $id Beacon beep id.
     *
     * @return BeaconBeepInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function getById(int $id): BeaconBeepInterface;

    /**
     * Delete beacon beep by id.
     *
     * @param int $id Beacon beep id.
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteById(int $id): void;

    /**
     * Returns true if the provided beep already exists in repository/database.
     *
     * @param BeaconBeepInterface $item Beacon beep.
     *
     * @return bool
     */
    public function alreadyExists(BeaconBeepInterface $item): bool;
}
