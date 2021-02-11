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

namespace Smile\ElasticsuiteBeacon\Api\Data;

/**
 * Beacon Beep interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
interface BeaconBeepInterface
{
    /**
     * Name of the main DB Table.
     */
    const TABLE_NAME    = 'smile_elasticsuite_beacon_beep';

    /**
     * Constant for field beep_id
     */
    const BEEP_ID         = 'beep_id';

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
     * Constant for field store_url
     */
    const STORE_URL        = 'store_url';

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
     * Get Beep ID
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
     *
     * @return string
     */
    public function getHostId();

    /**
     * Get Store URL
     *
     * @return string
     */
    public function getStoreUrl();

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
     * Set Beep ID
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     *
     * @param int $id Beep ID
     *
     * @return BeaconBeepInterface
     */
    public function setId($id);

    /**
     * Set Client ID
     *
     * @param string $clientId Client ID
     *
     * @return BeaconBeepInterface
     */
    public function setClientId($clientId);

    /**
     * Set Hostname
     *
     * @param string $hostname Hostname
     *
     * @return BeaconBeepInterface
     */
    public function setHostname($hostname);

    /**
     * Set Host ID
     *
     * @param string $hostId Host ID
     *
     * @return BeaconBeepInterface
     */
    public function setHostId($hostId);

    /**
     * Set Store URL
     *
     * @param string $storeUrl Store URL
     *
     * @return BeaconBeepInterface
     */
    public function setStoreUrl($storeUrl);

    /**
     * Set Created At
     *
     * @param string $createdAt Created At
     *
     * @return BeaconBeepInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Created At Date
     *
     * @param string $createdAtDate Created At Date
     *
     * @return BeaconBeepInterface
     */
    public function setCreatedAtDate($createdAtDate);

    /**
     * Set Magento Edition
     *
     * @param string $edition Magento edition
     *
     * @return BeaconBeepInterface
     */
    public function setMagentoEdition($edition);

    /**
     * Set Magento Version
     *
     * @param string $version Magento version
     *
     * @return BeaconBeepInterface
     */
    public function setMagentoVersion($version);

    /**
     * Set module data
     *
     * @param string $moduleData Module data
     *
     * @return BeaconBeepInterface
     */
    public function setModuleData($moduleData);
}
