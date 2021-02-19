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

use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;

/**
 * Interface ContextProviderInterface
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface ContextProviderInterface
{
    /**
     * Populates beacon beep data according to context.
     *
     * @param BeaconBeepInterface $beaconBeep Beacon beep interface.
     * @param array               $eventData  Tracker event data, if available.
     *
     * @return BeaconBeepInterface
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface;
}
