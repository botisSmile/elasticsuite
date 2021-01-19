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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBipInterface;


/**
 * Elasticsuite Beacon bip setup.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class BeaconBipSetup
{
    /**
     * Creates the beacon bips table.
     *
     * @param SchemaSetupInterface $setup Schema setup
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createBeaconBipTable(SchemaSetupInterface $setup)
    {
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
        if (!$setup->getConnection()->isTableExists($setup->getTable(BeaconBipInterface::TABLE_NAME))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable(BeaconBipInterface::TABLE_NAME))
                ->addColumn(
                    BeaconBipInterface::BIP_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true]
                )
                ->addColumn(
                    BeaconBipInterface::CLIENT_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::HOST_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    64,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::HOSTNAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::MAGENTO_EDITION,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::MAGENTO_VERSION,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::CREATED_AT_DATE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBipInterface::MODULE_DATA,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true]
                )
                ->addIndex(
                    $setup->getIdxName(
                        BeaconBipInterface::TABLE_NAME,
                        [BeaconBipInterface::CLIENT_ID, BeaconBipInterface::HOST_ID, BeaconBipInterface::CREATED_AT_DATE],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [BeaconBipInterface::CLIENT_ID, BeaconBipInterface::HOST_ID, BeaconBipInterface::CREATED_AT_DATE],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $setup->getConnection()->createTable($table);
        }
    }
}
