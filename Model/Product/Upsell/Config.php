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
     * @var array
     */
    private $mappings = [];

    /**
     * Config constructor.
     *
     * @param ContainerConfigurationFactory $containerConfigFactory Container configuration factory.
     * @param SearchableFieldFilter         $searchFieldFilter      Searchable field filter.
     */
    public function __construct(
        ContainerConfigurationFactory $containerConfigFactory,
        SearchableFieldFilter $searchFieldFilter
    ) {
        $this->containerConfigFactory = $containerConfigFactory;
        $this->searchFieldFilter      = $searchFieldFilter;
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
        $fields = [
            MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD,
            MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD . '.' . FieldInterface::ANALYZER_SHINGLE,
        ];

        foreach ($this->getMapping($storeId)->getFields() as $field) {
            $isTextField = $field->getType() == FieldInterface::FIELD_TYPE_TEXT;
            if ($isTextField && $field->isSearchable() && ($field->isUsedForSortBy() || $field->isFilterable())) {
                $fields[] = $field->getMappingProperty(FieldInterface::ANALYZER_STANDARD);
            }
        }

        return array_values(array_filter($fields));
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
}
