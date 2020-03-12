<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Setup\OptimizerCustomerSegmentSetupFactory;

/**
 * Install Schema for Catalog Optimizer Customer Segment Module
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var OptimizerCustomerSegmentSetup
     */
    private $optimizerCustomerSegmentSetup;

    /**
     * InstallSchema constructor.
     *
     * @param OptimizerCustomerSegmentSetupFactory $optimizerCustomerSegmentSetupFactory Setup Factory
     */
    public function __construct(OptimizerCustomerSegmentSetupFactory $optimizerCustomerSegmentSetupFactory)
    {
        $this->optimizerCustomerSegmentSetup = $optimizerCustomerSegmentSetupFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->optimizerCustomerSegmentSetup->createOptimizerCustomerSegmentTable($setup);
        $setup->endSetup();
    }
}
