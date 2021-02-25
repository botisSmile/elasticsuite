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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBeacon\Model\ContextProvider;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Module\ModuleListInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;
use Smile\ElasticsuiteBeacon\Model\ContextProviderInterface;

/**
 * Class Modules
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Modules implements ContextProviderInterface
{
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Modules constructor.
     * @param ModuleListInterface $moduleList Module list.
     * @param Json                $serializer JSON serializer.
     */
    public function __construct(ModuleListInterface $moduleList, Json $serializer)
    {
        $this->moduleList   = $moduleList;
        $this->serializer   = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(BeaconBeepInterface $beaconBeep, $eventData = []): BeaconBeepInterface
    {
        $modules = $this->moduleList->getNames();
        $elasticModules = array_filter(
            $modules,
            function ($module) {
                return preg_match('/elastic/i', $module);
            }
        );

        try {
            $beaconBeep->setModuleData(
                $this->serializer->serialize(array_values($elasticModules))
            );
        } catch (\Exception $e) {
            $beaconBeep->setModuleData($this->serializer->serialize([]));
        }

        return $beaconBeep;
    }
}
