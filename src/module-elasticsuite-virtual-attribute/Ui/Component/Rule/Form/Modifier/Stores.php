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
 * Used to populate "store_id" field according to current value of "store_id" for current rule.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Stores implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
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
        $rule = $this->locator->getRule();

        if ($rule && $rule->getId() && !empty($rule->getStores()) && empty($data[$rule->getId()]['store_id'])) {
            $data[$rule->getId()]['store_id'] = $rule->getStores();
        }

        if ($rule && $rule->getAttributeId() && !$this->isScopeStore($rule->getAttributeId())) {
            $data[$rule->getId()]['store_id'] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $rule = $this->locator->getRule();

        $meta['general']['children']['storeviews']['arguments']['data']['config']['visible'] = false;
        if ($rule && $rule->getAttributeId() && $this->isScopeStore($rule->getAttributeId())) {
            $meta['general']['children']['storeviews']['arguments']['data']['config']['visible'] = true;
        }

        return $meta;
    }

    /**
     * Check if an attribute is store scoped.
     *
     * @param int $attributeId The attribute Id
     *
     * @return array
     */
    private function isScopeStore($attributeId)
    {
        $attribute = $this->attributeRepository->get($attributeId);

        return ($attribute->getAttributeId() && $attribute->isScopeStore());
    }
}
