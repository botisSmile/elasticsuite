<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeacon\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface BeaconBipSearchResultsInterface
 *
 * @¢ategory Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface BeaconBipSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get beacon bips.
     *
     * @return BeaconBipInterface[]
     */
    public function getItems();

    /**
     * Set beacon bips.
     *
     * @param BeaconBipInterface[] $items Beacon bips.
     *
     * @return self
     */
    public function setItems(array $items);
}
