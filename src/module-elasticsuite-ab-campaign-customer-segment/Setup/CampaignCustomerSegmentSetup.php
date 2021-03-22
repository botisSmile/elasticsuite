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

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Smile\ElasticsuiteAbCampaignCustomerSegment\Api\Data\CampaignCustomerSegmentInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Campaign Customer Segment setup
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignCustomerSegmentSetup
{
    /**
     * Create Campaign Customer Segment link table.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup Setup instance
     */
    public function createCampaignCustomerSegmentTable(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->isTableExists($setup->getTable(CampaignCustomerSegmentInterface::TABLE_NAME))) {
            $campaignCustomerSegmentTable = $setup->getConnection()
                ->newTable($setup->getTable(CampaignCustomerSegmentInterface::TABLE_NAME))
                ->addColumn(
                    CampaignCustomerSegmentInterface::CAMPAIGN_ID,
                    Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'unsigned' => true, 'primary' => true],
                    'Campaign ID'
                )
                ->addColumn(
                    'segment_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Segment ID'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignCustomerSegmentInterface::TABLE_NAME,
                        CampaignCustomerSegmentInterface::CAMPAIGN_ID,
                        CampaignInterface::TABLE_NAME,
                        CampaignInterface::CAMPAIGN_ID
                    ),
                    CampaignCustomerSegmentInterface::CAMPAIGN_ID,
                    $setup->getTable(CampaignInterface::TABLE_NAME),
                    CampaignInterface::CAMPAIGN_ID,
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName(
                        CampaignCustomerSegmentInterface::TABLE_NAME,
                        CampaignCustomerSegmentInterface::SEGMENT_ID,
                        'magento_customersegment_segment',
                        'segment_id'
                    ),
                    CampaignCustomerSegmentInterface::SEGMENT_ID,
                    $setup->getTable('magento_customersegment_segment'),
                    'segment_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        CampaignCustomerSegmentInterface::TABLE_NAME,
                        [CampaignCustomerSegmentInterface::SEGMENT_ID]
                    ),
                    [CampaignCustomerSegmentInterface::SEGMENT_ID]
                )
                ->setComment('Campaign customer segment Table');

            $setup->getConnection()->createTable($campaignCustomerSegmentTable);
        }
    }
}
