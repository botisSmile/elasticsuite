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
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipSearchResultsInterface;

/**
 * Interface BeaconBipRepositoryInterface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface BeaconBipRepositoryInterface
{
    /**
     * Save beacon bip.
     *
     * @param BeaconBipInterface $item Beacon bip.
     *
     * @return BeaconBipInterface
     * @throws AlreadyExistsException
     */
    public function save(BeaconBipInterface $item): BeaconBipInterface;

    /**
     * Delete beacon bip.
     *
     * @param BeaconBipInterface $item Beacon bip.
     *
     * @return void
     * @throws \Exception
     */
    public function delete(BeaconBipInterface $item): void;

    /**
     * Get a list of beacon bips.
     *
     * @param SearchCriteriaInterface $searchCriteria Search criteria.
     *
     * @return BeaconBipSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : BeaconBipSearchResultsInterface;

    /**
     * Get beacon bip by id.
     *
     * @param int $id Beacon bip id.
     *
     * @return BeaconBipInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id) : BeaconBipInterface;

    /**
     * Delete beacon bip by id.
     *
     * @param int $id Beacon bip id.
     *
     * @return void
     * @throws \Exception
     */
    public function deleteById(int $id): void;
}
