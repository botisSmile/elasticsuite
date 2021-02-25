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
