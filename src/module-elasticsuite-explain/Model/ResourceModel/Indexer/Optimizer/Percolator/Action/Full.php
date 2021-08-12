<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\ResourceModel\Indexer\Optimizer\Percolator\Action;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory as OptimizerCollectionFactory;
use Smile\ElasticsuiteCore\Model\ResourceModel\Indexer\AbstractIndexer;
use Zend_Db_Expr as DbExpr;

/**
 * Optimizer fulltext/percolator indexer full action handler resource model
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class Full extends AbstractIndexer
{
    /** @var OptimizerCollectionFactory */
    protected $optimizerCollectionFactory;

    /**
     * Constructor.
     *
     * @param ResourceConnection         $resource                   Database adapter.
     * @param StoreManagerInterface      $storeManager               Store manager.
     * @param OptimizerCollectionFactory $optimizerCollectionFactory Optimizer collection factory
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        OptimizerCollectionFactory $optimizerCollectionFactory
    ) {
        parent::__construct($resource, $storeManager);
        $this->optimizerCollectionFactory = $optimizerCollectionFactory;
    }

    /**
     * Load a bulk of rule data.
     *
     * @param int        $storeId      Store id.
     * @param array|null $optimizerIds Optimizer ids filter
     * @param int        $fromId       Load from rule id greater than.
     * @param int        $limit        Number of rules to load.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSearchableOptimizers(
        int $storeId,
        ?array $optimizerIds = null,
        int $fromId = 0,
        int $limit = 100
    ): array {
        $collection = $this->optimizerCollectionFactory->create();
        if ($optimizerIds !== null) {
            $collection->addFieldToFilter('main_table.optimizer_id', ['in' => $optimizerIds]);
        }
        $collection->addFieldToFilter('main_table.optimizer_id', ['gt' => $fromId])
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToSelect(['is_active', 'optimizer_id'])
            ->addFieldToSelect(['optimizer_name' => 'name'])
            ->setOrder('main_table.optimizer_id', $collection::SORT_ORDER_ASC)
            ->setPageSize($limit);

        // Add search container field.
        $collection
            ->join(
                ['search_container' => $this->resource->getTableName('smile_elasticsuite_optimizer_search_container')],
                'search_container.optimizer_id = main_table.optimizer_id',
                ['search_container' => new DbExpr("GROUP_CONCAT(search_container.search_container)")]
            )
            ->getSelect()
            ->group('main_table.optimizer_id');

        return $this->connection->fetchAll($collection->getSelect());
    }
}
