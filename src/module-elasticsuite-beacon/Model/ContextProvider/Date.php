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

namespace Smile\ElasticsuiteBeacon\Model\ContextProvider;

use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Date
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Date implements ContextProviderInterface
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * Date constructor.
     *
     * @param TimezoneInterface $dateTime Datetime.
     */
    public function __construct(TimezoneInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $currentDate = $this->dateTime->date();
        $beaconBeep->setCreatedAt($currentDate->format(\DateTimeInterface::ISO8601));
        $beaconBeep->setCreatedAtDate($currentDate->setTime(0, 0)->format(\DateTimeInterface::ISO8601));

        return $beaconBeep;
    }
}
