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
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipInterface;

/**
 * Class BeaconBip
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class BeaconBip extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Saves beacon bip data.
     *
     * @param BeaconBipInterface $beaconBip Beacon bip.
     *
     * @return BeaconBip
     * @throws CouldNotSaveException
     */
    public function saveBipData(BeaconBipInterface $beaconBip)
    {
        if ($beaconBip->hasData()) {
            try {
                /** @var \Smile\ElasticsuiteBeacon\Model\BeaconBip $beaconBip */
                $bipData = $this->_prepareDataForSave($beaconBip);
                $this->getConnection()->insertOnDuplicate(
                    $this->getMainTable(),
                    $bipData,
                    array_keys($bipData)
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There is an error while saving comment.'));
            }
        }

        return $this;
    }

    /**
     * Saves beacon bip data.
     *
     * @param BeaconBipInterface $beaconBip Beacon bip.
     *
     * @return BeaconBip
     * @throws CouldNotSaveException
     */
    public function saveBipDataSoft(BeaconBipInterface $beaconBip)
    {
        if ($beaconBip->hasData()) {
            /** @var \Smile\ElasticsuiteBeacon\Model\BeaconBip $beaconBip */
            try {
                $bipData = $this->_prepareDataForSave($beaconBip);
                /** @var Mysql $connection */
                $connection = $this->getConnection();
                $connection->insertArray(
                    $this->getMainTable(),
                    array_keys($bipData),
                    $bipData,
                    AdapterInterface::INSERT_IGNORE
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There is an error while saving comment.'));
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(BeaconBipInterface::TABLE_NAME, BeaconBipInterface::BIP_ID);
    }

    /**
     * Filter beacon bip data against storage table fields.
     *
     * @param array $data Beacon bip data.
     *
     * @return array
     */
    protected function prepareDataForSave($data)
    {
        $fields = $this->getConnection()->describeTable(BeaconBipInterface::TABLE_NAME);

        return array_intersect_key($data, $fields);
    }
}
