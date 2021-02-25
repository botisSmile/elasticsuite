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


namespace Smile\ElasticsuiteBeacon\Cron;

use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\BeaconBeep\Exporter\ExporterException;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBeep as BeaconBeepResource;
use Smile\ElasticsuiteBeacon\Model\BeaconBeep\Exporter\Transport;

/**
 * Class Exporter
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class BeepExporter
{
    /**
     * @var BeaconBeepResource
     */
    private $beepResource;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Exporter constructor.
     *
     * @param BeaconBeepResource $beepResource Beacon beep resource model.
     * @param Transport          $transport    Beacon exporter transport.
     * @param LoggerInterface    $logger       Logger.
     */
    public function __construct(
        BeaconBeepResource $beepResource,
        Transport $transport,
        LoggerInterface $logger
    ) {
        $this->beepResource = $beepResource;
        $this->transport    = $transport;
        $this->logger       = $logger;
    }

    /**
     * Export all beacon beeps.
     *
     * @return BeepExporter
     * @throws ExporterException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        foreach ($this->getExportableDailyBeeps() as $day => $beeps) {
            $this->logger->info(__("Elasticsuite premium beacon: Exporting %1 beeps for %2", count($beeps), $day));
            if ($this->transport->send($beeps)) {
                $this->beepResource->deleteByIds(array_column($beeps, BeaconBeepInterface::BEEP_ID));
            }
        }
        $this->logger->info(__("Elasticsuite premium beacon: beeps export complete."));

        return $this;
    }

    /**
     * Get all exportable beeps, grouped by date
     *
     * @return \Generator
     */
    private function getExportableDailyBeeps()
    {
        $exportableDays = array_reverse($this->beepResource->getExportableDays());
        if (!empty($exportableDays)) {
            do {
                $day = array_pop($exportableDays);
                $beeps = $this->beepResource->getSpecificDayBeeps($day);
                if (!empty($beeps)) {
                    yield $day => $beeps;
                }
            } while (!empty($exportableDays));
        }
    }
}
