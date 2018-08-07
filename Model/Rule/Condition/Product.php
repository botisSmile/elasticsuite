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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition;

use \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Smile Elastic Suite Virtual Attribute Product Condition model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * Product constructor.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param \Magento\Rule\Model\Condition\Context                            $context               Context
     * @param \Magento\Backend\Helper\Data                                     $backendData           Backend Data
     * @param \Magento\Eav\Model\Config                                        $config                EAV Config
     * @param \Magento\Catalog\Model\ProductFactory                            $productFactory        Product Factory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                  $productRepository     Product Repository
     * @param \Magento\Catalog\Model\ResourceModel\Product                     $productResource       Product Resource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection     Attribute Set Collection
     * @param \Magento\Framework\Locale\FormatInterface                        $localeFormat          Locale Format
     * @param RuleCollectionFactory                                            $ruleCollectionFactory Rule Collection
     * @param array                                                            $data                  Data
     * @param \Magento\Catalog\Model\ProductCategoryList|null                  $categoryList          Category List
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        RuleCollectionFactory $ruleCollectionFactory,
        array $data = [],
        $categoryList = null
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );

        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string'      => ['{}', '!{}'],
                'numeric'     => ['==', '!=', '>=', '>', '<=', '<'],
                'date'        => ['==', '>=', '>', '<=', '<'],
                'select'      => ['==', '!='],
                'boolean'     => ['==', '!='],
                'multiselect' => ['()', '!()'],
                'grid'        => ['()', '!()'],
                'category'    => ['()', '!()'],
            ];

            $this->_arrayInputTypes = ['multiselect', 'grid', 'category'];
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * {@inheritDoc}
     */
    public function getInputType()
    {
        $inputType        = 'string';
        $selectAttributes = ['attribute_set_id'];

        if (in_array($this->getAttribute(), $selectAttributes)) {
            $inputType = 'select';
        } elseif ($this->getAttribute() === 'price') {
            $inputType = 'numeric';
        } elseif (is_object($this->getAttributeObject())) {
            $frontendInput = $this->getAttributeObject()->getFrontendInput();

            if ($this->getAttributeObject()->getAttributeCode() === 'category_ids') {
                $inputType = 'category';
            } elseif (in_array($frontendInput, ['select', 'multiselect'])) {
                $inputType = 'multiselect';
            } elseif ($frontendInput === 'date') {
                $inputType = 'date';
            } elseif ($frontendInput === 'boolean') {
                $inputType = 'boolean';
            }
        }

        return $inputType;
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        $valueElementType = 'text';

        if ($this->getAttribute() == 'attribute_set_id') {
            $valueElementType = 'select';
        } elseif (is_object($this->getAttributeObject())) {
            $frontendInput = $this->getAttributeObject()->getFrontendInput();

            if ($frontendInput === 'boolean') {
                $valueElementType = 'select';
            } elseif ($frontendInput === 'date') {
                $valueElementType = 'date';
            } elseif (in_array($frontendInput, ['select', 'multiselect'])) {
                $valueElementType = 'multiselect';
            }
        }

        return $valueElementType;
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = parent::getValueElementChooserUrl();

        if ($this->getAttribute() === 'category_ids') {
            $url              = 'smile_elasticsuite_virtual_attribute/rule_category/chooser/attribute/';
            $chooserUrlParams = [];
            if ($this->getJsFormObject()) {
                $chooserUrlParams['form'] = $this->getJsFormObject();
            }

            $url = $this->_backendData->getUrl($url, $chooserUrlParams);
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');

            if ($this->isArrayOperatorType()) {
                if (is_string($value)) {
                    $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
                }
            } elseif (is_array($value) && isset($value[0]) && is_string($value[0])) {
                $value = $value[0];
            }

            $this->setValueParsed($value);
        }

        return $this->getData('value_parsed');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritdoc}
     */
    protected function _setSelectOptions($selectOptions, $selectReady, $hashedReady)
    {
        if ($selectOptions !== null) {
            $this->filterSelectOptions($selectOptions);
        }

        parent::_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * Filter current select options to exclude values that are built by virtual attribute rules.
     *
     * @param array $selectOptions The option list to filter.
     */
    private function filterSelectOptions(&$selectOptions)
    {
        $ruleCollection = $this->ruleCollectionFactory->create();
        if ($this->getAttributeObject()) {
            $ruleCollection->addAttributeFilter($this->getAttributeObject());
        }

        $optionsIds    = array_map('intval', $ruleCollection->getAllOptionIds());
        $selectOptions = array_filter($selectOptions, function ($option) use ($optionsIds) {
            if (!isset($option['value']) || is_array($option['value'])) {
                return false;
            }

            return !in_array((int) $option['value'], $optionsIds);
        });
    }
}
