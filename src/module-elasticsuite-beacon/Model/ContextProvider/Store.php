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

namespace Smile\ElasticsuiteBeacon\Model\ContextProvider;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Store
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Store implements ContextProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Store constructor.
     * @param StoreManagerInterface $storeManager Store manager.
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $storeUrl = $this->storeManager->getDefaultStoreView()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        $beaconBeep->setStoreUrl($storeUrl);

        return $beaconBeep;
    }
}
