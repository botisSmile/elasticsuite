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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Smile\ElasticsuiteAbCampaignCustomerSegment\Setup\CampaignCustomerSegmentSetupFactory;

/**
 * Install Schema for Ab Campaign Customer Segment Module
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CampaignCustomerSegmentSetup
     */
    private $campaignCustomerSegmentSetup;

    /**
     * InstallSchema constructor.
     *
     * @param CampaignCustomerSegmentSetupFactory $optimizerCustomerSegmentSetupFactory Setup Factory
     */
    public function __construct(CampaignCustomerSegmentSetupFactory $optimizerCustomerSegmentSetupFactory)
    {
        $this->campaignCustomerSegmentSetup = $optimizerCustomerSegmentSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->campaignCustomerSegmentSetup->createCampaignCustomerSegmentTable($setup);
        $setup->endSetup();
    }
}
