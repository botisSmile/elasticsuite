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
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeacon\Api\Data;

/**
 * Beacon Bip interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface BeaconBipInterface
{
    /**
     * Name of the main DB Table.
     */
    const TABLE_NAME = 'smile_elasticsuite_beacon_bip';

    /*
        bip id // no for insert ignore
        customer identifier
        hostname
        host id
        date
        date (day)
        magento edition
        magento version
        (module name)
        module data
    */

    /**
     * Constant for field bip_id
     */
    const BIP_ID         = 'bip_id';

    /**
     * Constant for field client_id
     */
    const CLIENT_ID       = 'client_id';

    /**
     * Constant for field hostname
     */
    const HOSTNAME        = 'hostname';

    /**
     * Constant for field host_id
     */
    const HOST_ID         = 'host_id';

    /**
     * Constant for field created_at
     */
    const CREATED_AT      = 'created_at';

    /**
     * Constant for field created_at_date
     */
    const CREATED_AT_DATE = 'created_at_date';

    /**
     * Constant for Magento edition.
     */
    const MAGENTO_EDITION = 'magento_edition';

    /**
     * Constant for Magento version.
     */
    const MAGENTO_VERSION = 'magento_version';

    /**
     * Constant for field module_data
     */
    const MODULE_DATA     = 'module_data';

    /**
     * Get Bip ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Client ID
     *
     * @return string
     */
    public function getClientId();

    /**
     * Get Hostname
     *
     * @return string
     */
    public function getHostname();

    /**
     * Get Host ID
     * @return string
     */
    public function getHostId();

    /**
     * Get Created At
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Get Created At Date
     *
     * @return mixed
     */
    public function getCreatedAtDate();

    /**
     * Get Magento Edition
     *
     * @return string
     */
    public function getMagentoEdition();

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion();

    /**
     * Get module data
     *
     * @return string
     */
    public function getModuleData();

    /**
     * Set Bip ID
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     *
     * @param int $id Bip ID
     *
     * @return BeaconBipInterface
     */
    public function setId($id);

    /**
     * Set Client ID
     *
     * @param string $clientId Client ID
     *
     * @return BeaconBipInterface
     */
    public function setClientId($clientId);

    /**
     * Set Hostname
     *
     * @param string $hostname Hostname
     *
     * @return BeaconBipInterface
     */
    public function setHostname($hostname);

    /**
     * Set Host ID
     *
     * @param string $hostId Host ID
     *
     * @return BeaconBipInterface
     */
    public function setHostId($hostId);

    /**
     * Set Created At
     *
     * @param string $createdAt Created At
     *
     * @return BeaconBipInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Created At Date
     *
     * @param string $createdAtDate Created At Date
     *
     * @return BeaconBipInterface
     */
    public function setCreatedAtDate($createdAtDate);

    /**
     * Set Magento Edition
     *
     * @param string $edition Magento edition
     *
     * @return BeaconBipInterface
     */
    public function setMagentoEdition($edition);

    /**
     * Set Magento Version
     *
     * @param string $version Magento version
     *
     * @return BeaconBipInterface
     */
    public function setMagentoVersion($version);

    /**
     * Set module data
     *
     * @param string $moduleData Module data
     *
     * @return BeaconBipInterface
     */
    public function setModuleData($moduleData);
}
