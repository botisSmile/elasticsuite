<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Model\Campaign\CustomerSegment;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaignCustomerSegment\Model\ResourceModel\Campaign\CustomerSegment;

/**
 * Campaign Customer Segment Save Handler
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var CustomerSegment
     */
    private $resource;

    /**
     * SaveHandler constructor.
     *
     * @param CustomerSegment $resource Resource
     */
    public function __construct(
        CustomerSegment $resource
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
     * Retrieve customer segment ids limitation for the current campaign, if any.
     *
     * @param CampaignInterface $entity The campaign being saved
     * @return array
     */
    private function getCustomerSegmentIdsLimitation($entity)
    {
        /** @var Campaign $entity */
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
