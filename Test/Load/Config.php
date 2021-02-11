<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBeaconTest
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeaconTest\Test\Load;

use Smile\ElasticsuiteBeacon\Model\Config as DefaultConfig;

/**
 * Class Config
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeaconTest
 */
class Config extends DefaultConfig
{
    /**
     * {@inheritDoc}
     */
    public function getClientId(): string
    {
        return self::DEFAULT_CLIENT_ID;
    }
}
