<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\Optimizer\CustomerSegment;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Optimizer Customer Segment Read Handler
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class ReadHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * @var \Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\ResourceModel\Optimizer\CustomerSegment
     */
    private $resource;

    /**
     * ReadHandler constructor.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\ResourceModel\Optimizer\CustomerSegment $resource Resource
     */
    public function __construct(
        \Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\ResourceModel\Optimizer\CustomerSegment $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $this->setCustomerSegmentLimitation($entity);
        }

        return $entity;
    }

    /**
     * Retrieve and set customer segment ids limitation for the current optimizer, if any.
     *
     * @param OptimizerInterface $entity The optimizer being saved.
     *
     * @return void
     */
    private function setCustomerSegmentLimitation($entity)
    {
        $segmentIds = $this->resource->getSegmentIdsByOptimizer($entity);
        $containerData = [
            'segment_ids' => $segmentIds,
        ];
        $entity->setData('customer_segment', $containerData);
    }
}
