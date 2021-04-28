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

namespace Smile\ElasticsuiteExplain\Ui\Component\Optimizer\Listing;

use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;

/**
 * Optimizer collection processor interface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
interface OptimizerCollectionProcessorInterface
{
    /**
     * Process optimizer collection.
     *
     * @param OptimizerCollection $collection Optimizer collection
     * @return void
     */
    public function process(OptimizerCollection $collection);
}
