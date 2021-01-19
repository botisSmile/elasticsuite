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


namespace Smile\ElasticsuiteBeacon\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;

/**
 * Beacon beep model.
 *
 * @SuppressWarnings(CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class BeaconBeep extends AbstractModel implements BeaconBeepInterface, IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'smile_elasticsuite_beacon_beep';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * {@inheritDoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::BEEP_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getClientId()
    {
        return (string) $this->getData(self::CLIENT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getHostname()
    {
        return (string) $this->getData(self::HOSTNAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getHostId()
    {
        return (string) $this->getData(self::HOST_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreUrl()
    {
        return (string) $this->getData(self::STORE_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAtDate()
    {
        return (string) $this->getData(self::CREATED_AT_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getMagentoEdition()
    {
        return (string) $this->getData(self::MAGENTO_EDITION);
    }

    /**
     * {@inheritDoc}
     */
    public function getMagentoVersion()
    {
        return (string) $this->getData(self::MAGENTO_VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleData()
    {
        return (string) $this->getData(self::MODULE_DATA);
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function setId($id)
    {
        return $this->setData(self::BEEP_ID, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function setClientId($clientId)
    {
        return $this->setData(self::CLIENT_ID, $clientId);
    }

    /**
     * {@inheritDoc}
     */
    public function setHostname($hostname)
    {
        return $this->setData(self::HOSTNAME, $hostname);
    }

    /**
     * {@inheritDoc}
     */
    public function setHostId($hostId)
    {
        return $this->setData(self::HOST_ID, $hostId);
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreUrl($storeUrl)
    {
        return $this->setData(self::STORE_URL, $storeUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAtDate($createdAtDate)
    {
        return $this->setData(self::CREATED_AT_DATE, $createdAtDate);
    }

    /**
     * {@inheritDoc}
     */
    public function setMagentoEdition($edition)
    {
        return $this->setData(self::MAGENTO_EDITION, $edition);
    }

    /**
     * {@inheritDoc}
     */
    public function setMagentoVersion($version)
    {
        return $this->setData(self::MAGENTO_VERSION, $version);
    }

    /**
     * {@inheritDoc}
     */
    public function setModuleData($moduleData)
    {
        return $this->setData(self::MODULE_DATA, $moduleData);
    }
}
