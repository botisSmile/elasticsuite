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

use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteBeacon\Api\BeaconBeepRepositoryInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterfaceFactory;
use Smile\ElasticsuiteBeacon\Model\BeaconBeep\Generator as BeaconBeepGenerator;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Generator
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeaconTest
 */
class Generator extends BeaconBeepGenerator
{
    /**
     * @var string
     */
    private $name;

    /**
     * Generator constructor.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     *
     * @param BeaconBeepRepositoryInterface $beaconBeepRepository           Beacon beep repository.
     * @param BeaconBeepInterfaceFactory    $beaconBeepFactory              Beacon beep factory.
     * @param LoggerInterface               $logger                         Logger.
     * @param ContextProviderInterface[]    $initialContextProvidersPool    Initial context providers.
     * @param ContextProviderInterface[]    $additionalContextProvidersPool Additional context providers.
     * @param string                        $name                           Generator name.
     */
    public function __construct(
        BeaconBeepRepositoryInterface $beaconBeepRepository,
        BeaconBeepInterfaceFactory $beaconBeepFactory,
        LoggerInterface $logger,
        $initialContextProvidersPool = [],
        $additionalContextProvidersPool = [],
        $name = 'TestGenerator'
    ) {
        parent::__construct(
            $beaconBeepRepository,
            $beaconBeepFactory,
            $logger,
            $initialContextProvidersPool,
            $additionalContextProvidersPool
        );
        $this->name = $name;
    }

    /**
     * Get generator name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
