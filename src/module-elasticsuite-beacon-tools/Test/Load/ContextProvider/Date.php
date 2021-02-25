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

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Date
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeaconTools
 */
class Date implements ContextProviderInterface
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var integer
     */
    private $maxDays;

    /**
     * Date constructor.
     *
     * @param TimezoneInterface $dateTime Datetime.
     * @param integer           $maxDays  Maximum days in the past to generate data for.
     */
    public function __construct(TimezoneInterface $dateTime, $maxDays = 10)
    {
        $this->dateTime = $dateTime;
        $this->maxDays  = $maxDays;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $delay = rand(1, $this->maxDays);
        $currentDate = $this->dateTime->date(new \DateTime(sprintf('now -%dday', $delay)));
        $beaconBeep->setCreatedAt($currentDate->format(\DateTimeInterface::ISO8601));
        $beaconBeep->setCreatedAtDate($currentDate->setTime(0, 0)->format(\DateTimeInterface::ISO8601));

        return $beaconBeep;
    }
}
