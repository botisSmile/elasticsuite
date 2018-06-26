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
namespace Smile\ElasticsuiteVirtualAttribute\Ui\Component\Rule\Form\Attribute;

/**
 * Attributes options values for virtual attribute rule edit form.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributesCollectionFactory;

    /**
     * @var array|null
     */
    private $attributesList;

    /**
     * @var array
     */
    private $defaultAvailableFrontendInputs = ['select', 'multiselect'];

    /**
     * @var array
     */
    private $availableFrontendInputs = [];

    /**
     * Options constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributesCollectionFactory Attributes Collection
     * @param array                                                                    $availableFrontendInputs     Available Frontend inputs
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributesCollectionFactory,
        $availableFrontendInputs = []
    ) {
        $this->attributesCollectionFactory = $attributesCollectionFactory;
        $this->availableFrontendInputs     = array_merge($this->defaultAvailableFrontendInputs, $availableFrontendInputs);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAttributesList();
    }

    /**
     * Retrieve list of attributes that can be used to define virtual attributes rules.
     *
     * @return array
     */
    private function getAttributesList()
    {
        if (null === $this->attributesList) {
            $this->attributesList = [];

            $collection = $this->attributesCollectionFactory->create();
            $collection->addFieldToFilter('frontend_input', ['in' => $this->availableFrontendInputs]);

            foreach ($collection as $attribute) {
                $this->attributesList[$attribute->getId()] = [
                    'value' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                ];
            }
        }

        return $this->attributesList;
    }
}
