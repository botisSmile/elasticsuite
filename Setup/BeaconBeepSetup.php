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

namespace Smile\ElasticsuiteBeacon\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Smile\ElasticsuiteBeacon\Api\Data\BeaconBeepInterface;

/**
 * Elasticsuite Beacon beep setup.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class BeaconBeepSetup
{
    /**
     * Creates the beacon beeps table.
     *
     * @param SchemaSetupInterface $setup Schema setup
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createBeaconBeepTable(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(BeaconBeepInterface::TABLE_NAME))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable(BeaconBeepInterface::TABLE_NAME))
                ->addColumn(
                    BeaconBeepInterface::BEEP_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true]
                )
                ->addColumn(
                    BeaconBeepInterface::CLIENT_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::HOST_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    64,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::HOSTNAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::STORE_URL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::MAGENTO_EDITION,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::MAGENTO_VERSION,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::CREATED_AT,
                    // \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable'  => false]
                )
                ->addColumn(
                    BeaconBeepInterface::CREATED_AT_DATE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    BeaconBeepInterface::MODULE_DATA,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true]
                )
                ->addIndex(
                    $setup->getIdxName(
                        BeaconBeepInterface::TABLE_NAME,
                        [
                            BeaconBeepInterface::CLIENT_ID,
                            BeaconBeepInterface::HOST_ID,
                            BeaconBeepInterface::STORE_URL,
                            BeaconBeepInterface::CREATED_AT_DATE,
                        ],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        BeaconBeepInterface::CLIENT_ID,
                        BeaconBeepInterface::HOST_ID,
                        BeaconBeepInterface::STORE_URL,
                        BeaconBeepInterface::CREATED_AT_DATE,
                    ],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $setup->getConnection()->createTable($table);
        }
    }
}
