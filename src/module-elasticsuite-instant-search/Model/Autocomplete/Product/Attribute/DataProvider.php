<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product\Attribute;

use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\Item as AutocompleteItem;
use Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product\AttributeConfig;
use Smile\ElasticsuiteCatalog\Helper\Autocomplete as AutocompleteHelper;
use Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product\DataProvider as ProductDataProvider;

/**
 * Catalog product attributes autocomplete data provider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Autocomplete type
     */
    const AUTOCOMPLETE_TYPE = "product_attribute";

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var string Autocomplete result type
     */
    private $type;

    /**
     * @var AttributeConfig
     */
    private $attributeConfig;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var AutocompleteHelper
     */
    private $autocompleteHelper;

    /**
     * Constructor.
     *
     * @param ItemFactory         $itemFactory         Autocomplete item factory.
     * @param AttributeConfig     $attributeConfig     Autocomplete attribute config.
     * @param AutocompleteHelper  $autocompleteHelper  Autocomplete configuration helper.
     * @param ProductDataProvider $productDataProvider Product Data Provider.
     * @param string              $type                Autocomplete type code.
     */
    public function __construct(
        ItemFactory $itemFactory,
        AttributeConfig $attributeConfig,
        AutocompleteHelper $autocompleteHelper,
        ProductDataProvider $productDataProvider,
        $type = self::AUTOCOMPLETE_TYPE
    ) {
        $this->itemFactory         = $itemFactory;
        $this->type                = $type;
        $this->attributeConfig     = $attributeConfig;
        $this->autocompleteHelper  = $autocompleteHelper;
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * Returns autocomplete type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getItems()
    {
        $items = [];
        \Magento\Framework\Profiler::start('INSTANT_SEARCH::ATTRIBUTES::GET ITEMS');

        if ($this->autocompleteHelper->isEnabled($this->getType())) {
            foreach ($this->attributeConfig->getAutocompleteAttributes() ?? [] as $attribute) {
                $filterField = $attribute['filter_field'];
                $facetData   = $this->getFacetedData($filterField);

                foreach ($facetData as $filterValue => $currentFilter) {
                    if ($filterValue != '__other_docs') {
                        $itemData = [
                            'value'           => $filterValue,
                            'attribute_code'  => $attribute['code'],
                            'attribute_label' => $attribute['store_label'],
                            'type'            => $this->getType(),
                            'count'           => $currentFilter['count'],

                        ];
                        $items[] = $this->itemFactory->create($itemData);
                    }
                }
            }

            uasort($items, [$this, 'resultSorterCallback']);

            $items = array_slice($items, 0, $this->getResultsPageSize());
        }

        \Magento\Framework\Profiler::stop('INSTANT_SEARCH::ATTRIBUTES::GET ITEMS');

        return $items;
    }

    /**
     * Return field faceted data from faceted search result.
     *
     * @param string $field Facet field.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFacetedData($field)
    {
        $result = [];
        $aggregations = $this->productDataProvider->getQueryResponse()->getAggregations();

        $bucket = $aggregations->getBucket($field);

        if ($bucket) {
            foreach ($bucket->getValues() as $value) {
                $metrics = $value->getMetrics();
                $result[$value->getValue()] = $metrics;
            }
        }

        return $result;
    }

    /**
     * Retrieve number of products to display in autocomplete results
     *
     * @return int
     */
    private function getResultsPageSize()
    {
        return $this->autocompleteHelper->getMaxSize($this->getType());
    }

    /**
     * Sort autocomplete items by result count.
     *
     * @param AutocompleteItem $item1 First sorted item
     * @param AutocompleteItem $item2 Second sorted item
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @return integer
     */
    private function resultSorterCallback(AutocompleteItem $item1, AutocompleteItem $item2)
    {
        return $item2->getCount() - $item1->getCount();
    }
}
