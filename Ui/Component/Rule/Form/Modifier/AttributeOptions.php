<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Ui\Component\Rule\Form\Modifier;

/**
 * Smile Elastic Suite Virtual Attribute rule edit form data provider modifier :
 *
 * Used to populate "option_id" field according to current value of "attribute_id" for current rule.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AttributeOptions implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface
     */
    private $locator;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * AttributeOptions constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator             Rule Locator
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface                $attributeRepository Attribute Repository
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->locator             = $locator;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $rule = $this->locator->getRule();

        $options = [];
        if ($rule && $rule->getAttributeId()) {
            $options = $this->getAttributeOptions((int) $rule->getAttributeId());
        }

        $meta['general']['children']['option_id']['arguments']['data']['options']    = $options;
        $meta['general']['children']['option_label']['arguments']['data']['options'] = $options;

        $isNewRule          = (!$rule || !$rule->getId());
        $optionFieldVisible = $isNewRule && $rule && $rule->getAttributeId();

        $meta['general']['children']['option_id']['arguments']['data']['config']['disabled'] = !$isNewRule;
        $meta['general']['children']['option_id']['arguments']['data']['config']['visible']  = $optionFieldVisible;

        $meta['general']['children']['option_label']['arguments']['data']['config']['disabled'] = $isNewRule;
        $meta['general']['children']['option_label']['arguments']['data']['config']['visible']  = !$isNewRule;

        return $meta;
    }

    /**
     * Retrieve attribute options for a given attribute Id.
     *
     * @param int $attributeId The attribute Id
     *
     * @return array
     */
    private function getAttributeOptions($attributeId)
    {
        $attribute = $this->attributeRepository->get($attributeId);
        $options   = [];

        if ($attribute && $attribute->getAttributeId() && $attribute->getSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }

        return $options;
    }
}
