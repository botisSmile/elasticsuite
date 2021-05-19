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

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Optimizer\Listing\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Add store filter to collection strategy.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class AddStoreFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection $collection */
        $collection->addFilterToMap(
            OptimizerInterface::STORE_ID,
            sprintf('main_table.%s', OptimizerInterface::STORE_ID)
        );
        if ($field == OptimizerInterface::STORE_ID) {
            $collection->addFieldToFilter($field, $condition);
        }
    }
}
