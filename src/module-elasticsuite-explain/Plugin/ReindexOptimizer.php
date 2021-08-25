<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteExplain\Plugin;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;
use Smile\ElasticsuiteCatalogOptimizer\Model\OptimizerRepository;
use Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator;

/**
 * Reindex optimizer on save
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class ReindexOptimizer
{
    /**
     * @var Percolator
     */
    private $optimizerIndexer;

    /**
     * Constructor.
     *
     * @param Percolator $optimizerIndexer Optimizer indexer.
     */
    public function __construct(Percolator $optimizerIndexer)
    {
        $this->optimizerIndexer = $optimizerIndexer;
    }

    /**
     * Reindex optimizer on save
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param OptimizerRepository $subject   Optimizer repository
     * @param Optimizer           $optimizer Optimizer currently saved
     */
    public function afterSave(OptimizerRepository $subject, Optimizer $optimizer)
    {
        $this->optimizerIndexer->executeRow($optimizer->getId());
    }
}
