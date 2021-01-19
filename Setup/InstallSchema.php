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


namespace Smile\ElasticsuiteBeacon\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Smile\ElasticsuiteBeacon\Setup\BeaconBeepSetup;
use Smile\ElasticsuiteBeacon\Setup\BeaconBeepSetupFactory;


/**
 * Class InstallSchema
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var BeaconBeepSetup
     */
    private $beaconBeepSetup;

    /**
     * InstallSchema constructor.
     *
     * @param BeaconBeepSetupFactory $beaconBeepSetupFactory Setup Factory
     */
    public function __construct(BeaconBeepSetupFactory $beaconBeepSetupFactory)
    {
        $this->beaconBeepSetup = $beaconBeepSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->beaconBeepSetup->createBeaconBeepTable($setup);
        $setup->endSetup();
    }
}
