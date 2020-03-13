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
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\Optimizer\CustomerSegment;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Optimizer Customer Segment Save Handler
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class SaveHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * @var \Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\ResourceModel\Optimizer\CustomerSegment
     */
    private $resource;

    /**
     * SaveHandler constructor.
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
        $segmentIds = $this->getCustomerSegmentIdsLimitation($entity);

        $limitationData = [];

        foreach ($segmentIds as $segmentId) {
            $limitationData[] = ['segment_id' => $segmentId];
        }

        $this->resource->saveLimitation($entity, $limitationData);

        return $entity;
    }

    /**
     * Retrieve customer segment ids limitation for the current optimizer, if any.
     *
     * @param OptimizerInterface $entity The optimizer being saved
     *
     * @return array
     */
    private function getCustomerSegmentIdsLimitation($entity)
    {
        $customerSegmentData = $entity->getData('customer_segment');
        $applyTo     = is_array($customerSegmentData) ? ((bool) $customerSegmentData['apply_to'] ?? false) : false;
        $segmentIds  = ($applyTo === false) ? [] : $customerSegmentData['segment_ids'] ?? [];

        if (is_array(current($segmentIds))) {
            $ids = $segmentIds;
            $segmentIds = [];
            foreach ($ids as $segmentId) {
                if (isset($segmentId['id'])) {
                    $segmentIds[] = (int) $segmentId['id'];
                }
            }
        }

        return $segmentIds;
    }
}
