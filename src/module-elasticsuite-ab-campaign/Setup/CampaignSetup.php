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

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\EntityManager\MetadataPool;
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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * Class Constructor
     *
     * @param MetadataPool $metadataPool Metadata Pool.
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

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
                    Table::TYPE_SMALLINT,
                    5,
                    ['identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true]
                )
                ->addColumn(
                    CampaignInterface::NAME,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    CampaignInterface::AUTHOR_ID,
                    Table::TYPE_INTEGER,
                    10,
                    ['nullable' => true, 'unsigned' => true, 'default' => null]
                )
                ->addColumn(
                    CampaignInterface::AUTHOR_NAME,
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
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    CampaignInterface::END_DATE,
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false]
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
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignInterface::TABLE_NAME,
                        CampaignInterface::AUTHOR_ID,
                        'admin_user',
                        'user_id'
                    ),
                    CampaignInterface::AUTHOR_ID,
                    'admin_user',
                    'user_id',
                    AdapterInterface::FK_ACTION_SET_NULL
                );

            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * Create Optimizer Query table.
     *
     * @param SchemaSetupInterface $setup Setup instance
     */
    public function createCampaignSearchContainerTable(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(CampaignInterface::TABLE_NAME_SEARCH_CONTAINER))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable(CampaignInterface::TABLE_NAME_SEARCH_CONTAINER))
                ->addColumn(
                    CampaignInterface::CAMPAIGN_ID,
                    Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'unsigned' => true, 'primary' => true],
                    'Campaign ID'
                )
                ->addColumn(
                    CampaignInterface::SEARCH_CONTAINER,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'primary' => true],
                    'Search Container'
                )
                ->addColumn(
                    'apply_to',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'If this optimizer applies to specific entities or not.'
                )
                ->addIndex(
                    $setup->getIdxName(CampaignInterface::TABLE_NAME, [CampaignInterface::SEARCH_CONTAINER]),
                    [CampaignInterface::SEARCH_CONTAINER]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignInterface::TABLE_NAME_SEARCH_CONTAINER,
                        CampaignInterface::CAMPAIGN_ID,
                        CampaignInterface::TABLE_NAME,
                        CampaignInterface::CAMPAIGN_ID
                    ),
                    CampaignInterface::CAMPAIGN_ID,
                    $setup->getTable(CampaignInterface::TABLE_NAME),
                    CampaignInterface::CAMPAIGN_ID,
                    Table::ACTION_CASCADE
                )
                ->setComment('Query type per campaign table');

            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * Create table containing entity association between optimizer and category_id or search_terms.
     *
     * @param SchemaSetupInterface $setup Setup instance
     */
    public function createCampaignLimitationTable(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(CampaignInterface::TABLE_NAME_LIMITATION))) {
            $categoryIdField = $this->metadataPool->getMetadata(CategoryInterface::class)->getIdentifierField();

            $optimizerCategoryTable = $setup->getConnection()
                ->newTable($setup->getTable(CampaignInterface::TABLE_NAME_LIMITATION))
                ->addColumn(
                    CampaignInterface::CAMPAIGN_ID,
                    Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'unsigned' => true],
                    'Campaign ID'
                )
                ->addColumn(
                    'category_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => null],
                    'Category ID'
                )
                ->addColumn(
                    'query_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => null],
                    'Query ID'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignInterface::TABLE_NAME_LIMITATION,
                        CampaignInterface::CAMPAIGN_ID,
                        CampaignInterface::TABLE_NAME,
                        CampaignInterface::CAMPAIGN_ID
                    ),
                    CampaignInterface::CAMPAIGN_ID,
                    $setup->getTable(CampaignInterface::TABLE_NAME),
                    CampaignInterface::CAMPAIGN_ID,
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignInterface::TABLE_NAME_LIMITATION,
                        'category_id',
                        'catalog_category_entity',
                        $categoryIdField
                    ),
                    'category_id',
                    $setup->getTable('catalog_category_entity'),
                    $categoryIdField,
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName(CampaignInterface::TABLE_NAME_LIMITATION, 'query_id', 'search_query', 'query_id'),
                    'query_id',
                    $setup->getTable('search_query'),
                    'query_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        CampaignInterface::TABLE_NAME_LIMITATION,
                        [CampaignInterface::CAMPAIGN_ID, 'category_id', 'query_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [CampaignInterface::CAMPAIGN_ID, 'category_id', 'query_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Campaign limitation Table');

            $setup->getConnection()->createTable($optimizerCategoryTable);
        }
    }
}
