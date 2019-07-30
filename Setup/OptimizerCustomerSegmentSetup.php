<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Api\Data\OptimizerCustomerSegmentInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Catalog Optimizer Customer Segment setup
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class OptimizerCustomerSegmentSetup
{
    /**
     * Create Optimizer Customer Segment link table.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup Setup instance
     */
    public function createOptimizerCustomerSegmentTable(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(OptimizerCustomerSegmentInterface::TABLE_NAME))) {
            $optimizerCategoryTable = $setup->getConnection()
                ->newTable($setup->getTable(OptimizerCustomerSegmentInterface::TABLE_NAME))
                ->addColumn(
                    OptimizerCustomerSegmentInterface::OPTIMIZER_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'primary' => true],
                    'Optimizer ID'
                )
                ->addColumn(
                    'segment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Segment ID'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        OptimizerCustomerSegmentInterface::TABLE_NAME,
                        OptimizerCustomerSegmentInterface::OPTIMIZER_ID,
                        OptimizerInterface::TABLE_NAME,
                        OptimizerInterface::OPTIMIZER_ID
                    ),
                    OptimizerCustomerSegmentInterface::OPTIMIZER_ID,
                    $setup->getTable(OptimizerInterface::TABLE_NAME),
                    OptimizerInterface::OPTIMIZER_ID,
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName(
                        OptimizerCustomerSegmentInterface::TABLE_NAME,
                        OptimizerCustomerSegmentInterface::SEGMENT_ID,
                        'magento_customersegment_segment',
                        'segment_id'
                    ),
                    OptimizerCustomerSegmentInterface::SEGMENT_ID,
                    $setup->getTable('magento_customersegment_segment'),
                    'segment_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        OptimizerCustomerSegmentInterface::TABLE_NAME,
                        [OptimizerCustomerSegmentInterface::SEGMENT_ID]
                    ),
                    [OptimizerCustomerSegmentInterface::SEGMENT_ID]
                )
                ->setComment('Search optimizer customer segment Table');

            $setup->getConnection()->createTable($optimizerCategoryTable);
        }
    }
}
