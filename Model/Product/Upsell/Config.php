<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell;

use Smile\ElasticsuiteCore\Search\Request\Query\Fulltext\SearchableFieldFilter;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterfaceFactory as ContainerConfigurationFactory;
use Smile\ElasticsuiteCore\Api\Index\MappingInterface;
use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterface;
use Smile\ElasticsuiteRecommender\Helper\Data as DataHelper;
use Smile\ElasticsuiteCore\Helper\Mapping as MappingHelper;

/**
 * Upsell Config model
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Config
{
    /**
     * @var ContainerConfigurationFactory
     */
    private $containerConfigFactory;

    /**
     * @var SearchableFieldFilter
     */
    private $searchFieldFilter;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * @var array
     */
    private $mappings = [];

    /**
     * @var array
     */
    private $similarityFields = [];

    /**
     * Config constructor.
     *
     * @param ContainerConfigurationFactory $containerConfigFactory Container configuration factory.
     * @param SearchableFieldFilter         $searchFieldFilter      Searchable field filter.
     * @param DataHelper                    $helper                 Data helper.
     * @param MappingHelper                 $mappingHelper          Mapping helper.
     */
    public function __construct(
        ContainerConfigurationFactory $containerConfigFactory,
        SearchableFieldFilter $searchFieldFilter,
        DataHelper $helper,
        MappingHelper $mappingHelper
    ) {
        $this->containerConfigFactory = $containerConfigFactory;
        $this->searchFieldFilter      = $searchFieldFilter;
        $this->helper                 = $helper;
        $this->mappingHelper          = $mappingHelper;
    }

    /**
     * Return the products index fields to use for finding similar/"more like this" products
     *
     * @param int $storeId Store Id
     *
     * @return array
     */
    public function getSimilarityFields($storeId)
    {
        if (!isset($this->similarityFields[$storeId])) {
            $defaultFields = [
                MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD,
                MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD . '.' . FieldInterface::ANALYZER_SHINGLE,
            ];

            $fields = [];
            // Extract possible attribute related eligible fields from mapping.
            foreach ($this->getMapping($storeId)->getFields() as $field) {
                $isTextField = $field->getType() == FieldInterface::FIELD_TYPE_TEXT;
                if ($isTextField && $field->isSearchable() && ($field->isUsedForSortBy() || $field->isFilterable())) {
                    $fields[] = $field->getMappingProperty(FieldInterface::ANALYZER_STANDARD);
                }
            }
            $fields = array_filter($fields);

            // Build whitelisted list of fields from list of configured attributes.
            $attributeFields = $this->getAttributesFields($storeId);

            $allowedFields = array_intersect($fields, $attributeFields);

            if (empty($allowedFields)) {
                $allowedFields = $defaultFields;
            }

            $this->similarityFields[$storeId] = array_values($allowedFields);
        }

        return $this->similarityFields[$storeId];
    }

    /**
     * Return the weighted search fields to use when replaying past fulltext searches
     *
     * @param int $storeId Store Id to get weighted search fields for
     *
     * @return float[]
     */
    public function getWeightedSearchFields($storeId)
    {
        $mapping  = $this->getMapping($storeId);
        $analyzer = FieldInterface::ANALYZER_STANDARD;
        $defaultField = MappingInterface::DEFAULT_SEARCH_FIELD;

        return $mapping->getWeightedSearchProperties($analyzer, $defaultField, 1, $this->searchFieldFilter);
    }

    /**
     * Retrieve product mapping for the current store id.
     *
     * @param int $storeId Store id.
     *
     * @return MappingInterface
     */
    private function getMapping($storeId)
    {
        if (!isset($this->mappings[$storeId])) {
            $containerName = 'catalog_view_container';
            $container     = $this->containerConfigFactory->create(['containerName' => $containerName, 'storeId' => $storeId]);

            $this->mappings[$storeId] = $container->getMapping();
        }

        return $this->mappings[$storeId];
    }

    /**
     * Get the list of engine fields for the configured attributes
     *
     * @param int $storeId Store id.
     *
     * @return array
     */
    private function getAttributesFields($storeId)
    {
        $fields = [];

        $attributes = $this->helper->getSimilarityAttributes();
        foreach ($attributes as $attributeCode) {
            $fieldName = $this->getMappingFieldName($attributeCode, $storeId);
            try {
                $field = $this->getMapping($storeId)->getField($fieldName);
                $mappingProperty = $field->getMappingProperty(FieldInterface::ANALYZER_STANDARD);
                $fields[] = $mappingProperty;
            } catch (\Exception $e) {
                ;
            }
        }

        return array_filter($fields);
    }

    /**
     * Name of the field in the search engine mapping.
     *
     * @param string $fieldName Request field name.
     * @param int    $storeId   Store id.
     *
     * @return string
     */
    private function getMappingFieldName($fieldName, $storeId)
    {
        try {
            $optionTextFieldName = $this->mappingHelper->getOptionTextFieldName($fieldName);
            $this->getMapping($storeId)->getField($optionTextFieldName);
            $fieldName = $optionTextFieldName;
        } catch (\Exception $e) {
            ;
        }

        return $fieldName;
    }
}
