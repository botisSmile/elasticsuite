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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition;

/**
 * Smile Elastic Suite Virtual Attribute rule Combine model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * @var string
     */
    protected $type = self::class;

    /**
     * Combine constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context                    $context          Context
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory Condition Factory
     * @param array                                                    $data             Data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);

        $this->setType($this->type);
    }

    /**
     * {@inheritDoc}
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];

        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];

        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => $this->getType(),
                    'label' => __('Conditions Combination'),
                ],
                [
                    'label' => __('Product Attribute'),
                    'value' => $attributes,
                ],
            ]
        );

        return $conditions;
    }
}
