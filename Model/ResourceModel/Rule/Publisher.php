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
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy;

/**
 * Smile Elastic Suite Virtual Attribute rules Publisher.
 * Will process an insert from select based on values calculated by rules applier.
 * Also process fulltext reindex if needed.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Publisher extends AbstractDb
{
    /**
     * Default number of products that can be modified without invalidating the catalogsearch_fulltext index.
     */
    const DEFAULT_FULLTEXT_THRESHOLD = 10000;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var TableStrategy
     */
    private $tableStrategy;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $metadata;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private $attribute;

    /**
     * @var integer
     */
    private $fulltextIndexThreshold = self::DEFAULT_FULLTEXT_THRESHOLD;

    /**
     * Applier constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context        $context                Context
     * @param \Magento\Framework\Indexer\IndexerRegistry               $indexerRegistry        Indexer Registry
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository    Attribute Repository
     * @param \Magento\Framework\EntityManager\MetadataPool            $metadataPool           Metadata Pool
     * @param TableStrategy                                            $tableStrategy          Table Strategy
     * @param int                                                      $attributeId            Attribute Id
     * @param int                                                      $fulltextIndexThreshold Fulltext Index Threshold
     * @param null                                                     $connectionName         Connection Name
     *
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        TableStrategy $tableStrategy,
        int $attributeId,
        $fulltextIndexThreshold = self::DEFAULT_FULLTEXT_THRESHOLD,
        $connectionName = null
    ) {
        $this->indexerRegistry        = $indexerRegistry;
        $this->tableStrategy          = $tableStrategy;
        $this->attribute              = $attributeRepository->get($attributeId);
        $this->metadata               = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->fulltextIndexThreshold = $fulltextIndexThreshold;

        parent::__construct($context, $connectionName);
    }

    /**
     * Publish data for a given attribute Id : Computed data were stored in a temporary table
     * and are moved to the final destination table.
     *
     * @throws \Exception
     */
    public function publish()
    {
        $this->getConnection()->beginTransaction();

        try {
            $this->applyCalculatedDataToAttributeTable();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $idSelect = $this->getConnection()
            ->select()
            ->distinct(true)
            ->from(['main_table' => $this->getMainTable()], [])
            ->joinInner(
                ['entity' => $this->metadata->getEntityTable()],
                sprintf('main_table.%s = entity.%s', $this->metadata->getLinkField(), $this->metadata->getLinkField()),
                $this->metadata->getIdentifierField()
            )->group($this->metadata->getLinkField());

        $ids = $this->getConnection()->fetchCol($idSelect);
        $this->processFullTextReindex($ids);

        $this->dropTemporaryTable();
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->createTemporaryTable();

        // Init with temporary table as main table, this table will contain calculated values for this attribute Id.
        $this->_init($this->getTemporaryTableName(), 'value_id');
    }

    /**
     * Move computed data for all rules related to this attributes from the tmp table to the real attribute backend table.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyCalculatedDataToAttributeTable()
    {
        $targetColumns = [$this->metadata->getLinkField(), 'attribute_id', 'store_id', 'value'];
        $sourceColumns = [$this->metadata->getLinkField(), 'attribute_id', 'store_id', 'GROUP_CONCAT(value) as value'];

        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), $sourceColumns)
            ->group([$this->metadata->getLinkField(), 'attribute_id', 'store_id']);

        // Insert-Select from temporary table in one-shot.
        $query = $this->getConnection()->insertFromSelect(
            $select,
            $this->attribute->getBackendTable(),
            $targetColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
        );
        $this->getConnection()->query($query);

        // Remove attribute values rows that are containing only NULL as value.
        $condition = ['attribute_id = ?' => (int) $this->attribute->getAttributeId(), 'value IS NULL'];
        $this->getConnection()->delete($this->attribute->getBackendTable(), $condition);
    }

    /**
     * Create temporary table for current attribute.
     */
    private function createTemporaryTable()
    {
        $temporaryName = $this->getTemporaryTableName();
        $table         = $this->getConnection()->getTableName($temporaryName);

        // Drop the temporary table in case it already exists on this (persistent?) connection.
        $this->getConnection()->dropTemporaryTable($table);

        $this->getConnection()->createTemporaryTableLike(
            $table,
            $this->getConnection()->getTableName($this->attribute->getBackendTable()),
            true
        );

        if ($this->attribute->getFrontendInput() === 'multiselect') {
            // For multiselect, remove existing unique index if any.
            // Because each value is inserted on an unique data row.
            $indexList = $this->getConnection()->getIndexList($table);
            foreach ($indexList as $indexName => $ddl) {
                if (isset($ddl['type']) &&
                    $ddl['type'] === \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ) {
                    $this->getConnection()->dropIndex($table, $indexName);
                }
            }

            // Add index to table to manage proper insertion later.
            $this->getConnection()->addIndex(
                $table,
                'idx_primary',
                [$this->metadata->getLinkField(), 'attribute_id', 'store_id', 'value'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }
    }

    /**
     * Drop temporary table used for current product attribute.
     */
    private function dropTemporaryTable()
    {
        $temporaryName = $this->getTemporaryTableName();
        $this->getConnection()->dropTemporaryTable($temporaryName);
    }

    /**
     * Get temporary table name for current product attribute.
     *
     * @return string
     */
    private function getTemporaryTableName()
    {
        return $this->tableStrategy->getTemporaryTableName($this->attribute);
    }

    /**
     * Process full-text reindex for product ids
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @param mixed $ids The product ids to reindex
     */
    private function processFullTextReindex($ids)
    {
        $fullTextIndexer = $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        if (!$fullTextIndexer->isScheduled() && !empty($ids)) {
            if (count($ids) > $this->fulltextIndexThreshold) {
                $fullTextIndexer->invalidate();
            } else {
                $fullTextIndexer->reindexList($ids);
            }
        }
    }
}
