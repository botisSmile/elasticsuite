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

namespace Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBip\Command;

use Smile\ElasticsuiteBeacon\Model\ResourceModel\Command\InsertIgnore;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipInterface;

/**
 * Class Save
 *
 * @category Smile
 * @package  Smile\Elasticsuite
 */
class Save
{
    /**
     * @var InsertIgnore
     */
    private $insertIgnore;

    /**
     * Save constructor.
     * @param InsertIgnore $insertIgnore Insert ignore command.
     */
    public function __construct(InsertIgnore $insertIgnore)
    {
        $this->insertIgnore = $insertIgnore;
    }

    /**
     * Save beacon bip to database.
     *
     * @param BeaconBipInterface $beaconBip Beacon bip.
     *
     * @return void
     */
    public function execute(BeaconBipInterface $beaconBip): void
    {
        $data = $beaconBip->getData();
        $this->insertIgnore->execute(
            $data,
            BeaconBipInterface::TABLE_NAME,
            array_keys($data)
        );
    }
}
