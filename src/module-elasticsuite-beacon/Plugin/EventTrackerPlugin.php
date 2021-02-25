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

namespace Smile\ElasticsuiteBeacon\Plugin;

use Smile\ElasticsuiteTracker\Api\CustomerTrackingServiceInterface;
use Smile\ElasticsuiteBeacon\Model\BeaconBeep\Generator as BeaconBeepGenerator;

/**
 * Class EventTrackerPlugin
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class EventTrackerPlugin
{
    /**
     * @var BeaconBeepGenerator
     */
    private $generator;

    /**
     * EventTrackerPlugin constructor.
     *
     * @param BeaconBeepGenerator $generator Beacon beep generator.
     */
    public function __construct(BeaconBeepGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Before plugin - Creates a beacon beep and register it according to context.
     *
     * @param CustomerTrackingServiceInterface $trackingService Tracking service.
     * @param array                            $eventData       Tracker event data.
     *
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddEvent(CustomerTrackingServiceInterface $trackingService, $eventData)
    {
        $this->generator->generate($eventData);

        return null;
    }
}
