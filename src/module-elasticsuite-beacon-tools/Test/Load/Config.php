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

namespace Smile\ElasticsuiteBeaconTools\Test\Load;

use Smile\ElasticsuiteBeacon\Model\Config as DefaultConfig;

/**
 * Class Config
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeaconTools
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
