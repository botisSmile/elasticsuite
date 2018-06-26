<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Setup;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Smile Elastic Suite Virtual Attribute Setup
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class VirtualAttributeSetup
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Class Constructor
     *
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool Metadata Pool.
     */
    public function __construct(\Magento\Framework\EntityManager\MetadataPool $metadataPool)
    {
        $this->metadataPool = $metadataPool;
    }

    /**
     * Create Optimizer main table.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup Setup instance
     */
    public function createVirtualAttributeRuleTable(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(RuleInterface::TABLE_NAME))) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable(RuleInterface::TABLE_NAME))
                ->addColumn(
                    RuleInterface::RULE_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Rule ID'
                )
                ->addColumn(
                    RuleInterface::IS_ACTIVE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Is Rule Active'
                )
                ->addColumn(
                    RuleInterface::ATTRIBUTE_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Rule Attribute Id'
                )
                ->addColumn(
                    RuleInterface::OPTION_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Rule Option Id'
                )
                ->addColumn(
                    RuleInterface::CONDITION,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '',
                    [],
                    'Rule condition'
                )
                ->addForeignKey(
                    $setup->getFkName(RuleInterface::TABLE_NAME, 'attribute_id', 'eav_attribute', 'attribute_id'),
                    'attribute_id',
                    $setup->getTable('eav_attribute'),
                    'attribute_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Virtual Attribute rules table');

            $setup->getConnection()->createTable($table);
        }
    }
}
