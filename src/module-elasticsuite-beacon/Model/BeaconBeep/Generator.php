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

namespace Smile\ElasticsuiteBeacon\Model\BeaconBeep;

use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteBeacon\Api\BeaconBeepRepositoryInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterfaceFactory;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Generator
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Generator
{
    /**
     * @var BeaconBeepRepositoryInterface
     */
    private $beaconBeepRepository;

    /**
     * @var BeaconBeepInterfaceFactory
     */
    private $beaconBeepFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContextProviderInterface[]
     */
    private $initialContextProvidersPool;

    /**
     * @var ContextProviderInterface[]
     */
    private $additionalContextProvidersPool;

    /**
     * Generator constructor.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     *
     * @param BeaconBeepRepositoryInterface $beaconBeepRepository           Beacon beep repository.
     * @param BeaconBeepInterfaceFactory    $beaconBeepFactory              Beacon beep factory.
     * @param LoggerInterface               $logger                         Logger.
     * @param ContextProviderInterface[]    $initialContextProvidersPool    Context providers.
     * @param ContextProviderInterface[]    $additionalContextProvidersPool Context providers.
     */
    public function __construct(
        BeaconBeepRepositoryInterface $beaconBeepRepository,
        BeaconBeepInterfaceFactory $beaconBeepFactory,
        LoggerInterface $logger,
        $initialContextProvidersPool = [],
        $additionalContextProvidersPool = []
    ) {
        $this->beaconBeepRepository = $beaconBeepRepository;
        $this->beaconBeepFactory    = $beaconBeepFactory;
        $this->logger               = $logger;
        $this->initialContextProvidersPool      = $initialContextProvidersPool;
        $this->additionalContextProvidersPool   = $additionalContextProvidersPool;
    }

    /**
     * Before plugin - Creates a beacon beep and register it according to context.
     *
     * @param array $eventData Tracker event data, if available
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate($eventData = [])
    {
        /** @var BeaconBeepInterface $beaconBeep */
        $beaconBeep = $this->beaconBeepFactory->create();
        foreach ($this->initialContextProvidersPool as $contextProvider) {
            $beaconBeep = $contextProvider->apply($beaconBeep, $eventData);
        }
        if (!$this->beaconBeepRepository->alreadyExists($beaconBeep)) {
            foreach ($this->additionalContextProvidersPool as $contextProvider) {
                $beaconBeep = $contextProvider->apply($beaconBeep, $eventData);
            }

            try {
                $this->beaconBeepRepository->save($beaconBeep);
            } catch (\Exception $e) {
                $this->logger->error(__('Failed to register elasticsuite premium beacon beep'), ['exception' => $e]);
            }
        }
    }
}
