<?php

namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell;

use Smile\ElasticsuiteCore\Search\Request\Query\Fulltext\SearchableFieldFilter;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterfaceFactory as ContainerConfigurationFactory;
use Smile\ElasticsuiteCore\Api\Index\MappingInterface;
use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterface;

class Config
{
    /**
     * @var ContainerConfigurationFactory
     */
    private $containerConfigFactory;

    /**
     *
     * @var SearchableFieldFilter
     */
    private $searchFieldFilter;

    private $mappings = [];

    public function __construct(
        ContainerConfigurationFactory $containerConfigFactory,
        SearchableFieldFilter $searchFieldFilter
    ) {
        $this->containerConfigFactory = $containerConfigFactory;
        $this->searchFieldFilter      = $searchFieldFilter;
    }

    public function getSimilarityFields($storeId)
    {
        $fields = [MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD, MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD . '.' . FieldInterface::ANALYZER_SHINGLE];

        foreach ($this->getMapping($storeId)->getFields() as $field) {
            $isTextField = $field->getType() == FieldInterface::FIELD_TYPE_TEXT;
            if ($isTextField && $field->isSearchable() && ($field->isUsedForSortBy() || $field->isFilterable())) {
                $fields[] = $field->getMappingProperty(FieldInterface::ANALYZER_STANDARD);
            }
        }

        return array_values(array_filter($fields));
    }

    public function getWeightedSearchFields($storeId)
    {
        $mapping  = $this->getMapping($storeId);
        $analyzer = FieldInterface::ANALYZER_STANDARD;
        $defaultField = MappingInterface::DEFAULT_SEARCH_FIELD;

        return $mapping->getWeightedSearchProperties($analyzer, $defaultField, 1, $this->searchFieldFilter);
    }

    /**
     * Retrieve mapping for the current store id.
     *
     * @param int $storeId Store id.
     *
     * @return MappingInterface
     */
    private function getMapping($storeId)
    {
        if (!isset($this->mappings[$storeId])) {
            $searchContainerName = 'catalog_view_container';
            $searchContainer     = $this->containerConfigFactory->create(['containerName' => $searchContainerName, 'storeId' => $storeId]);

            $this->mappings[$storeId] = $searchContainer->getMapping();
        }

        return $this->mappings[$storeId];
    }
}