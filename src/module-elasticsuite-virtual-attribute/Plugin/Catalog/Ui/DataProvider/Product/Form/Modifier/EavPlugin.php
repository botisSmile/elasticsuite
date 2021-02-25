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
namespace Smile\ElasticsuiteVirtualAttribute\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Smile Elastic Suite Virtual Attribute product form Ui component plugin.
 * Used to add a tooltip around virtual attributes.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class EavPlugin
{
    /**
     * Template for tooltip added to virtual attributes in product edit form.
     */
    const TOOLTIP_TEMPLATE = 'Smile_ElasticsuiteVirtualAttribute/form/element/helper/tooltip';

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager          Array manager util.
     * @param RuleCollectionFactory                  $ruleCollectionFactory Rule Collection Factory
     */
    public function __construct(
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ) {
        $this->arrayManager          = $arrayManager;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Fix custom entity field meta.
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject   Object.
     * @param callable                                                   $proceed   Original method.
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface        $attribute Attribute.
     * @param string                                                     $groupCode Group code.
     * @param int                                                        $sortOrder Sort order.
     *
     * @return array
     */
    public function aroundSetupAttributeMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject,
        callable $proceed,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        $groupCode,
        $sortOrder
    ) {
        $meta = $proceed($attribute, $groupCode, $sortOrder);

        if ($this->hasCalculatedValues($attribute)) {
            $configPath = ltrim($subject::META_CONFIG_PATH, \Magento\Framework\Stdlib\ArrayManager::DEFAULT_PATH_DELIMITER);

            $fieldConfig = [
                'tooltip' => [
                    'description' => __("This attribute has values that can be automatically set by virtual attribute rules. "
                        . "Modifying it could lead to potential data loss on next occurence of rules calculation."),
                ],
                'tooltipTpl' => self::TOOLTIP_TEMPLATE,
            ];

            $meta = $this->arrayManager->merge($configPath, $meta, $fieldConfig);
        }

        return $meta;
    }

    /**
     * Check if an attribute has calculated values. (true if it has rules based on this attribute).
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute Attribute
     *
     * @return bool
     */
    private function hasCalculatedValues(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $result = false;

        if ($attribute->getAttributeId()) {
            $ruleCollection = $this->ruleCollectionFactory->create();
            $ruleCollection->addAttributeFilter($attribute);

            $result = $ruleCollection->getSize() > 0;
        }

        return $result;
    }
}
