<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBeaconTools
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBeaconTools\Test\Load\ContextProvider;

use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Host
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeaconTools
 */
class Host implements ContextProviderInterface
{
    /**
     * @var integer
     */
    private $maxHosts;

    /**
     * Host constructor.
     *
     * @param integer $maxHosts Maximum number of hosts to simulate.
     */
    public function __construct($maxHosts = 3)
    {
        $this->maxHosts = $maxHosts;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $dirName = dirname(__DIR__, 6);
        $randomHost = rand(1, $this->maxHosts);
        $dirInode = fileinode($dirName) . $randomHost;
        $hostId = md5($dirInode);
        $beaconBeep->setHostId($hostId);
        $beaconBeep->setHostname(gethostname());

        return $beaconBeep;
    }
}
