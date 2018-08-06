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
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy
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
     * Applier constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context                                  $context             Context
     * @param \Magento\Framework\Indexer\IndexerRegistry                                         $indexerRegistry     Indexer Registry
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy $tableStrategy       Table Strategy
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface                           $attributeRepository Attribute Repository
     * @param \Magento\Framework\EntityManager\MetadataPool                                      $metadataPool        Metadata Pool
     * @param int                                                                                $attributeId         Attribute Id
     * @param null                                                                               $connectionName      Connection Name
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\TableStrategy $tableStrategy,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        int $attributeId,
        $connectionName = null
    ) {
        $this->indexerRegistry     = $indexerRegistry;
        $this->tableStrategy       = $tableStrategy;
        $this->attribute           = $attributeRepository->get($attributeId);
        $this->metadata            = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
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
            $sourceColumns = $targetColumns = [$this->metadata->getLinkField(), 'attribute_id', 'store_id'];

            $select = $this->getConnection()->select()->from($this->getMainTable(), $sourceColumns);
            $query  = $this->getConnection()->insertFromSelect(
                $select,
                $this->attribute->getBackendTable(),
                $targetColumns,
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $this->getConnection()->query($query);
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
            );

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
     * Create temporary table for current attribute.
     */
    private function createTemporaryTable()
    {
        $temporaryName = $this->getTemporaryTableName();

        // Drop the temporary table in case it already exists on this (persistent?) connection.
        $this->getConnection()->dropTemporaryTable($temporaryName);

        $this->getConnection()->createTemporaryTableLike(
            $this->getConnection()->getTableName($temporaryName),
            $this->getConnection()->getTableName($this->attribute->getBackendTable()),
            true
        );
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
     *
     * @param mixed $ids The product ids to reindex
     */
    private function processFullTextReindex($ids)
    {
        $fullTextIndexer = $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        if (!$fullTextIndexer->isScheduled()) {
            if (!empty($ids)) {
                $fullTextIndexer->reindexList($ids);
            }
        }
    }
}
