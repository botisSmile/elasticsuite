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

namespace Smile\ElasticsuiteBeacon\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class BeaconBeep
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class BeaconBeep extends AbstractDb
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BeaconBeep constructor.
     * @param Context           $context        Db context.
     * @param TimezoneInterface $dateTime       Datetime.
     * @param LoggerInterface   $logger         Logger.
     * @param string            $connectionName Connection name.
     */
    public function __construct(
        Context $context,
        TimezoneInterface $dateTime,
        LoggerInterface $logger,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    /**
     * Saves beacon beep data.
     *
     * @param BeaconBeepInterface $beaconBeep Beacon beep.
     *
     * @return BeaconBeep
     * @throws CouldNotSaveException
     */
    public function saveBeepData(BeaconBeepInterface $beaconBeep)
    {
        if ($beaconBeep->hasData()) {
            /** @var \Smile\ElasticsuiteBeacon\Model\BeaconBeep $beaconBeep */
            try {
                $beepData = $this->_prepareDataForSave($beaconBeep);
                /** @var Mysql $connection */
                $connection = $this->getConnection();
                $connection->insertArray(
                    $this->getMainTable(),
                    array_keys($beepData),
                    [array_values($beepData)],
                    AdapterInterface::INSERT_IGNORE
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There was an error while saving beep.'));
            }
        }

        return $this;
    }

    /**
     * Load a bulk of beeps.
     *
     * @param int $fromId Load beeps with id greater than.
     * @param int $limit  Number of beeps to load.
     *
     * @return array
     */
    public function getExportableBeeps($fromId = 0, $limit = 100)
    {
        $connection = $this->getConnection();
        $currentDate = $this->dateTime->date();
        $currentDate->setTime(0, 0)->setTimezone(new \DateTimeZone('UTC'));

        $select = $connection->select()
            ->from($this->getTable(BeaconBeepInterface::TABLE_NAME));

        $select->limit($limit);
        $select->where(BeaconBeepInterface::BEEP_ID . ' > ?', $fromId);
        $select->where(BeaconBeepInterface::CREATED_AT_DATE . ' < ?', $currentDate->format('Y-m-d H:i:s'));
        $select->order(BeaconBeepInterface::BEEP_ID);

        return $connection->fetchAll($select);
    }

    /**
     * Get all available days to export.
     *
     * @return array
     */
    public function getExportableDays()
    {
        $connection = $this->getConnection();
        $currentDate = $this->dateTime->date();
        $currentDate->setTime(0, 0)->setTimezone(new \DateTimeZone('UTC'));
        $select = $connection->select()
            ->from($this->getTable(BeaconBeepInterface::TABLE_NAME), [BeaconBeepInterface::CREATED_AT_DATE])
            ->distinct(true)
            ->where(BeaconBeepInterface::CREATED_AT_DATE . ' < ?', $currentDate->format('Y-m-d H:i:s'))
            ->order(BeaconBeepInterface::CREATED_AT_DATE);

        return $connection->fetchCol($select);
    }

    /**
     * Get beeps for a specific day.
     *
     * @param string $date Created at date as stored internally.
     *
     * @return array
     */
    public function getSpecificDayBeeps($date)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable(BeaconBeepInterface::TABLE_NAME))
            ->where(BeaconBeepInterface::CREATED_AT_DATE . ' = ?', $date)
            ->order(BeaconBeepInterface::BEEP_ID);

        return $connection->fetchAll($select);
    }

    /**
     * Delete a group of beeps by ids.
     *
     * @param array $beepIds Beep ids.
     * @param int   $limit   Maximum number of beeps to delete at once.
     *
     * @return BeaconBeep
     */
    public function deleteByIds($beepIds = [], $limit = 100)
    {
        $connection = $this->getConnection();

        $chunks = array_chunk($beepIds, $limit);
        foreach ($chunks as $someBeepIds) {
            $connection->delete(
                $this->getTable(BeaconBeepInterface::TABLE_NAME),
                $connection->quoteInto(BeaconBeepInterface::BEEP_ID . ' IN(?)', $someBeepIds)
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(BeaconBeepInterface::TABLE_NAME, BeaconBeepInterface::BEEP_ID);
    }
}
