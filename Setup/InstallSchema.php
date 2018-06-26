<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Fanny DECLERCK <fadec@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Setup;

use Smile\ElasticsuiteVirtualAttribute\Setup\VirtualAttributeSetupFactory;

/**
 * Install Schema for Catalog Optimizer Module
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Fanny DECLERCK <fadec@smile.fr>
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
    public function __construct(VirtualAttributeSetupFactory $virtualAttributeSetupFactory)
    {
        $this->virtualAttributeSetup = $virtualAttributeSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->virtualAttributeSetup->createVirtualAttributeRuleTable($setup);

        $setup->endSetup();
    }
}
