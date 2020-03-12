<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Api\Data;

/**
 * Elasticsuite Catalog Optimizer Customer Segment Interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 */
interface OptimizerCustomerSegmentInterface
{
    /**
     * Name of the main Mysql Table
     */
    const TABLE_NAME = 'smile_elasticsuite_optimizer_customer_segment';

    /**
     * Constant for field optimizer_id
     */
    const OPTIMIZER_ID = 'optimizer_id';

    /**
     * Constant for field segment_id
     */
    const SEGMENT_ID = 'segment_id';
}
