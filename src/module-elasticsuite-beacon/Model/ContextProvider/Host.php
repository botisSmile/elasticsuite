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

namespace Smile\ElasticsuiteBeacon\Model\ContextProvider;

use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Host
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Host implements ContextProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $dirName = dirname(__DIR__, 5);
        $dirInode = fileinode($dirName);
        $hostId = md5($dirInode);

        $beaconBeep->setHostId($hostId);
        $beaconBeep->setHostname(gethostname());

        return $beaconBeep;
    }
}
