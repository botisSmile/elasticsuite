<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBeacon
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBeep;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Smile\ElasticsuiteBeacon\Model\BeaconBeep as Model;
use Smile\ElasticsuiteBeacon\Model\ResourceModel\BeaconBeep as ResourceModel;

/**
 * Class Collection
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}
