<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\Store;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Elasticsuite Campaign setup.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignSetup
{
    /**
     * Creates the campaign table.
     *
     * @param SchemaSetupInterface $setup Schema setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createCampaignTable(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(CampaignInterface::TABLE_NAME))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable(CampaignInterface::TABLE_NAME))
                ->addColumn(
                    CampaignInterface::CAMPAIGN_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true]
                )
                ->addColumn(
                    CampaignInterface::NAME,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    CampaignInterface::AUTHOR,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    CampaignInterface::STORE_ID,
                    Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'default' => Store::DEFAULT_STORE_ID, 'unsigned' => true]
                )
                ->addColumn(
                    CampaignInterface::STATUS,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    CampaignInterface::DESCRIPTION,
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true]
                )
                ->addColumn(
                    CampaignInterface::CREATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                )
                ->addColumn(
                    CampaignInterface::START_DATE,
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => null]
                )
                ->addColumn(
                    CampaignInterface::END_DATE,
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => null]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignInterface::TABLE_NAME,
                        CampaignInterface::STORE_ID,
                        'store',
                        Store::STORE_ID
                    ),
                    CampaignInterface::STORE_ID,
                    'store',
                    Store::STORE_ID,
                    Table::ACTION_CASCADE
                );

            $setup->getConnection()->createTable($table);
        }
    }
}
