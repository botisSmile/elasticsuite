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

namespace Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator\Action;

use \Smile\ElasticsuiteExplain\Model\ResourceModel\Indexer\Optimizer\Percolator\Action\Full as ResourceModel;
use Traversable;

/**
 * Optimizer fulltext/percolator indexer full action handler
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class Full
{
    /** @var ResourceModel */
    protected $resourceModel;

    /**
     * Constructor.
     *
     * @param ResourceModel $resourceModel Indexer resource model.
     */
    public function __construct(ResourceModel $resourceModel)
    {
        $this->resourceModel  = $resourceModel;
    }

    /**
     * Get data for a list of optimizers in a store id.
     * If the list of optimizer ids is null, all rules data will be loaded.
     *
     * @param int        $storeId     Store id.
     * @param array|null $optimizeIds List of target rule ids.
     *
     * @return Traversable
     */
    public function rebuildStoreIndex(int $storeId, ?array $optimizeIds = null): Traversable
    {
        $lastOptimizerId = 0;
        do {
            $optimizers = $this->getSearchableOptimizers($storeId, $optimizeIds, $lastOptimizerId);
            foreach ($optimizers as $optimizerData) {
                $optimizerData['search_container'] = explode(',', $optimizerData['search_container']);
                $lastOptimizerId = (int) $optimizerData['optimizer_id'];
                yield $lastOptimizerId => $optimizerData;
            }
        } while (!empty($optimizers));
    }

    /**
     * Load a bulk of optimizers data.
     *
     * @param int        $storeId Store id.
     * @param array|null $ruleIds Target rule ids filter
     * @param int        $fromId  Load from rule id greater than.
     * @param int        $limit   Number of rules to load.
     *
     * @return array
     */
    private function getSearchableOptimizers(
        int $storeId,
        ?array $ruleIds = null,
        int $fromId = 0,
        int $limit = 100
    ): array {
        return $this->resourceModel->getSearchableOptimizers($storeId, $ruleIds, $fromId, $limit);
    }
}
