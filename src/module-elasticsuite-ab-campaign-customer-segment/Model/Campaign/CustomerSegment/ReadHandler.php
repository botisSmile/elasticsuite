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
 * Campaign Customer Segment Read Handler
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var CustomerSegment
     */
    private $resource;

    /**
     * ReadHandler constructor.
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
        if ($entity->getId()) {
            $this->setCustomerSegmentLimitation($entity);
        }

        return $entity;
    }

    /**
     * Retrieve and set customer segment ids limitation for the current campaign, if any.
     *
     * @param CampaignInterface $entity The campaign being saved.
     * @return void
     */
    private function setCustomerSegmentLimitation($entity)
    {
        /** @var Campaign $entity */
        $segmentIds = $this->resource->getSegmentIdsByCampaign($entity);
        $containerData = [
            'segment_ids' => $segmentIds,
        ];
        $entity->setData('customer_segment', $containerData);
    }
}
