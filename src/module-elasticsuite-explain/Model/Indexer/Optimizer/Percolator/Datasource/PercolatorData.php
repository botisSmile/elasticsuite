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

namespace Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator\Datasource;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory as OptimizerCollectionFactory;
use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;
use Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Request\Query\Builder as QueryBuilder;

/**
 * Class PercolatorData
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class PercolatorData implements DatasourceInterface
{
    /** The percolator type for this entity */
    const PERCOLATOR_TYPE = 'optimizer';

    /** @var OptimizerCollectionFactory */
    protected $optimizerCollectionFactory;

    /** @var QueryBuilder; */
    protected $queryBuilder;

    /**
     * PercolatorData constructor.
     *
     * @param OptimizerCollectionFactory $optimizerCollectionFactory Optimizer collection factory
     * @param QueryBuilder               $queryBuilder               ES query builder
     */
    public function __construct(
        OptimizerCollectionFactory $optimizerCollectionFactory,
        QueryBuilder $queryBuilder
    ) {
        $this->optimizerCollectionFactory = $optimizerCollectionFactory;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Add rule percolator data to the index
     * {@inheritdoc}
     */
    public function addData($storeId, array $indexData)
    {
        $optimizerCollection = $this->optimizerCollectionFactory->create()
            ->setFlag('do_not_run_after_load', true)
            ->addFieldToFilter('optimizer_id', array_keys($indexData));

        /** @var Optimizer $optimizer */
        foreach ($optimizerCollection as $optimizer) {
            $query = $this->queryBuilder->buildQuery($optimizer->getRuleCondition()->getSearchQuery());
            $percolatorData = [
                'type' => 'optimizer',
                'percolator_type' => self::PERCOLATOR_TYPE,
                'query' => $query,
            ];

            $optimizerData = $indexData[$optimizer->getId()] + $percolatorData;
            if (isset($optimizerData['is_active'])) {
                $optimizerData['is_active'] = (bool) $optimizerData['is_active'];
            }

            $indexData[$optimizer->getId()] = $optimizerData;
        }

        return $indexData;
    }
}
