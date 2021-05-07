<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Plugin\CatalogOptimizer\ResourceModel\Optimizer;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory as OptimizerCollectionFactory;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;

/**
 * Optimizer collection factory plugin.
 * Prevents column names collision when joining campaign optimizers related tables.
 *
 * @category   Smile
 * @package    Smile\ElasticsuiteAbCampaign
 * @author     Richard Bayet <richard.bayet@smile.fr>
 * @deprecated Call to addFilterToMap done in filter strategies for optimizers list/grid component.
 */
class CollectionFactoryPlugin
{
    /**
     * After plugin - Create optimizer collection instance.
     *
     * @param OptimizerCollectionFactory $factory    Optimizer collection factory.
     * @param OptimizerCollection        $collection Optimizer collection.
     *
     * @return OptimizerCollection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(OptimizerCollectionFactory $factory, OptimizerCollection $collection)
    {
        $collection->addFilterToMap(
            OptimizerInterface::NAME,
            sprintf('main_table.%s', OptimizerInterface::NAME)
        );
        $collection->addFilterToMap(
            OptimizerInterface::STORE_ID,
            sprintf('main_table.%s', OptimizerInterface::STORE_ID)
        );

        return $collection;
    }
}
