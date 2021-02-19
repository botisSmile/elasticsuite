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

use Magento\Framework\App\ProductMetadataInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Magento
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Magento implements ContextProviderInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Magento constructor.
     * @param ProductMetadataInterface $productMetadata Product metadata.
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $beaconBeep->setMagentoVersion($this->productMetadata->getVersion())
            ->setMagentoEdition($this->productMetadata->getEdition());

        return $beaconBeep;
    }
}
