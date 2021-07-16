<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Smile\ElasticsuiteAbCampaign\Setup\CampaignSetup;

/**
 * Class InstallSchema
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class UpgradeSchema implements UpgradeSchemaInterface
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
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->campaignSetup->createCampaignSessionTable($setup);
        }
        $setup->endSetup();
    }
}
