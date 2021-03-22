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

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Api\Data;

/**
 * Elasticsuite Campaign Customer Segment Interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 */
interface CampaignCustomerSegmentInterface
{
    /**
     * Name of the main Mysql Table
     */
    const TABLE_NAME = 'smile_elasticsuite_campaign_customer_segment';

    /**
     * Constant for field campaign_id
     */
    const CAMPAIGN_ID = 'campaign_id';

    /**
     * Constant for field segment_id
     */
    const SEGMENT_ID = 'segment_id';
}
