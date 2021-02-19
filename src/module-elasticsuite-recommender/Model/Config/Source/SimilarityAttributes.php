<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Config\Source;

use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Indexer\Fulltext\Datasource\AttributeData as ProductAttributeData;
use Smile\ElasticsuiteCatalog\Helper\ProductAttribute as ProductAttributeHelper;
use Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product\AttributeList;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterfaceFactory as ContainerConfigurationFactory;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCore\Helper\Mapping as MappingHelper;
use Smile\ElasticsuiteCore\Api\Index\MappingInterface;
use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SimilarityAbleFieldFilter;

/**
 * Class SimilarityAttributes
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class SimilarityAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ContainerConfigurationFactory
     */
    private $containerConfigFactory;

    /**
     * @var ProductAttributeHelper
     */
    private $attributeHelper;

    /**
     * @var ProductAttributeData
     */
    private $productAttributeData;

    /**
     * @var AttributeList
     */
    private $attributeList;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Index\MappingInterface
     */
    private $mapping;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * @var SimilarityAbleFieldFilter
     */
    private $similarityFieldField;

    /**
     * Options array
     *
     * @var array
     */
    protected $options;

    /**
     * SimilarityAttributes constructor.
     *
     * @param ContainerConfigurationFactory $containerConfigFactory Container configuration factory.
     * @param ProductAttributeHelper        $attributeHelper        Product attributes helper.
     * @param ProductAttributeData          $productAttributeData   Product attributes data resource.
     * @param AttributeList                 $attributeList          Indexed product attributes list.
     * @param StoreManagerInterface         $storeManager           Store manager.
     * @param MappingHelper                 $mappingHelper          Mapping helper.
     * @param SimilarityAbleFieldFilter     $similarityFieldFilter  Similarity mapping field filter.
     */
    public function __construct(
        ContainerConfigurationFactory $containerConfigFactory,
        ProductAttributeHelper $attributeHelper,
        ProductAttributeData $productAttributeData,
        AttributeList $attributeList,
        StoreManagerInterface $storeManager,
        MappingHelper $mappingHelper,
        SimilarityAbleFieldFilter $similarityFieldFilter
    ) {
        $this->containerConfigFactory = $containerConfigFactory;
        $this->attributeHelper = $attributeHelper;
        $this->productAttributeData = $productAttributeData;
        $this->attributeList = $attributeList;
        $this->storeManager = $storeManager;
        $this->mappingHelper = $mappingHelper;
        $this->similarityFieldField = $similarityFieldFilter;
        $this->options = [];
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $attributeCollection = $this->productAttributeData->addIndexedFilterToAttributeCollection(
            $this->attributeList->getAttributeCollection()
        );

        foreach ($attributeCollection as $attribute) {
            if ($this->isValidForSimilarity($attribute)) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $this->options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                ];
            }
        }

        return $this->options;
    }

    /**
     * Returns true if the provided attribute validates extra conditions for being used for a similarity/mlt request.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute Attribute
     *
     * @return bool
     */
    private function isValidForSimilarity($attribute)
    {
        $isValid = false;

        $fieldName = $this->getMappingFieldName($attribute->getAttributeCode());
        try {
            $field = $this->getMapping()->getField($fieldName);
            if ($this->similarityFieldField->filterField($field)) {
                $mappingProperty = $field->getMappingProperty(FieldInterface::ANALYZER_STANDARD);
                $isValid = ($mappingProperty !== null);
            }
        } catch (\Exception $e) {
            ;
        }

        return $isValid;
    }

    /**
     * Return the field name for a given attribute in the search engine mapping.
     *
     * @param string $attributeCode Attribute code.
     *
     * @return string
     */
    private function getMappingFieldName($attributeCode)
    {
        $fieldName = $attributeCode;

        try {
            $optionTextFieldName = $this->mappingHelper->getOptionTextFieldName($fieldName);
            $this->getMapping()->getField($optionTextFieldName);
            $fieldName = $optionTextFieldName;
        } catch (\Exception $e) {
            ;
        }

        return $fieldName;
    }

    /**
     * Retrieve product mapping.
     *
     * @return MappingInterface
     */
    private function getMapping()
    {
        if (!isset($this->mapping)) {
            $containerName  = 'catalog_view_container';
            $storeId        = $this->getDefaultStoreView()->getId();
            $container      = $this->containerConfigFactory->create(['containerName' => $containerName, 'storeId' => $storeId]);

            $this->mapping = $container->getMapping();
        }

        return $this->mapping;
    }

    /**
     * Retrieve default Store View
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    private function getDefaultStoreView()
    {
        $store = $this->storeManager->getDefaultStoreView();
        if (null === $store) {
            // Occurs when current user does not have access to default website (due to AdminGWS ACLS on Magento EE).
            $store = current($this->storeManager->getWebsites())->getDefaultStore();
        }

        return $store;
    }
}
