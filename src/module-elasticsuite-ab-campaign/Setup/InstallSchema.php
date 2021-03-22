<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class InstallSchema
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CampaignSetup
     */
    private $campaignSetup;

    /**
     * InstallSchema constructor.
     *
     * @param CampaignSetupFactory $campaignSetupFactory Setup Factory
     */
    public function __construct(CampaignSetupFactory $campaignSetupFactory)
    {
        $this->campaignSetup = $campaignSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->campaignSetup->createCampaignTable($setup);
        $this->campaignSetup->createCampaignSearchContainerTable($setup);
        $this->campaignSetup->createCampaignLimitationTable($setup);
        $setup->endSetup();
    }
}
