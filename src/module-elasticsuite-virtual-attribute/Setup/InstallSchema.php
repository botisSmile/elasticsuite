<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Setup;

/**
 * Install Schema for Virtual Attributes rule module.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @var VirtualAttributeSetup
     */
    private $virtualAttributeSetup;

    /**
     * InstallSchema constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Setup\VirtualAttributeSetupFactory $virtualAttributeSetupFactory Setup Factory
     */
    public function __construct(\Smile\ElasticsuiteVirtualAttribute\Setup\VirtualAttributeSetupFactory $virtualAttributeSetupFactory)
    {
        $this->virtualAttributeSetup = $virtualAttributeSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->virtualAttributeSetup->createVirtualAttributeRuleTables($setup);

        $setup->endSetup();
    }
}
