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

namespace Smile\ElasticsuiteBeacon\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface BeaconBeepSearchResultsInterface
 *
 * @¢ategory Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface BeaconBeepSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get beacon beeps.
     *
     * @return BeaconBeepInterface[]
     */
    public function getItems();

    /**
     * Set beacon beeps.
     *
     * @param BeaconBeepInterface[] $items Beacon beeps.
     *
     * @return self
     */
    public function setItems(array $items);
}
