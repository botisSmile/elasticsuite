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

namespace Smile\ElasticsuiteExplain\Model\Indexer\Optimizer;

use ArrayObject;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator\Action\Full;

/**
 * Optimizers fulltext/percolator indexer
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class Percolator implements IndexerActionInterface, MviewActionInterface
{
    /** Elasticsearch index alias */
    const INDEXER_ID = 'elasticsuite_optimizer_percolators';

    /** Elasticsearch index identifier */
    const INDEX_IDENTIFIER = 'optimizer';

    /** @var IndexerInterface */
    private $indexerHandler;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var DimensionFactory */
    private $dimensionFactory;

    /** @var Full */
    private $fullAction;

    /**
     * Constructor
     *
     * @param Full                  $fullAction       Full index action
     * @param IndexerInterface      $indexerHandler   Index handler
     * @param StoreManagerInterface $storeManager     Store Manager
     * @param DimensionFactory      $dimensionFactory Dimension factory
     */
    public function __construct(
        Full $fullAction,
        IndexerInterface $indexerHandler,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory
    ) {
        $this->fullAction = $fullAction;
        $this->indexerHandler = $indexerHandler;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $this->indexerHandler->deleteIndex([$dimension], new ArrayObject($ids));
            $this->indexerHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $this->indexerHandler->cleanIndex([$dimension]);
            $this->indexerHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function executeRow($identifier)
    {
        $this->execute([$identifier]);
    }
}
