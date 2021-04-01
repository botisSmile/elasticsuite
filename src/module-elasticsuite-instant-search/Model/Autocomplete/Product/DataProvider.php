<?php
/**
 * DISCLAIMER
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

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product;

use Magento\Framework\Search\SearchEngineInterface;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\Item as TermItem;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalog\Helper\Autocomplete as ConfigurationHelper;
use Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider as TermDataProvider;
use Smile\ElasticsuiteCore\Search\Request\Builder as RequestBuilder;
use Smile\ElasticsuiteCore\Search\RequestInterface;
use Smile\ElasticsuiteInstantSearch\Model\Search\QueryStringProvider;
use Smile\ElasticsuiteInstantSearch\Model\Search\QueryStringProviderFactory;

/**
 * Catalog product autocomplete data provider.
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
    const AUTOCOMPLETE_TYPE = "product";

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var ConfigurationHelper
     */
    private $configurationHelper;

    /**
     * @var string Autocomplete result type
     */
    private $type;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var TermDataProvider
     */
    private $termDataProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponse
     */
    private $queryResponse;

    /**
     * @var \Smile\ElasticsuiteInstantSearch\Model\Search\QueryStringProviderFactory
     */
    private $queryStringProviderFactory;

    /**
     * Constructor.
     *
     * @param ItemFactory                $itemFactory                Suggest item factory.
     * @param ConfigurationHelper        $configurationHelper        Autocomplete configuration helper.
     * @param RequestBuilder             $requestBuilder             Search Request Builder.
     * @param SearchEngineInterface      $searchEngineInterface      Search Engine Interface.
     * @param TermDataProvider           $termDataProvider           Terms Data Provider.
     * @param QueryStringProviderFactory $queryStringProviderFactory Query String provider factory.
     * @param StoreManagerInterface      $storeManager               Store Manager Interface.
     * @param string                     $type                       Autocomplete provider type.
     * @param string                     $searchRequestName          Search Request Name.
     */
    public function __construct(
        ItemFactory $itemFactory,
        ConfigurationHelper $configurationHelper,
        RequestBuilder $requestBuilder,
        SearchEngineInterface $searchEngineInterface,
        TermDataProvider $termDataProvider,
        QueryStringProviderFactory $queryStringProviderFactory,
        StoreManagerInterface $storeManager,
        $type = self::AUTOCOMPLETE_TYPE,
        $searchRequestName = 'catalog_product_autocomplete'
    ) {
        $this->itemFactory                = $itemFactory;
        $this->configurationHelper        = $configurationHelper;
        $this->requestBuilder             = $requestBuilder;
        $this->searchEngine               = $searchEngineInterface;
        $this->termDataProvider           = $termDataProvider;
        $this->storeManager               = $storeManager;
        $this->type                       = $type;
        $this->searchRequestName          = $searchRequestName;
        $this->queryStringProviderFactory = $queryStringProviderFactory;
    }

    /**
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
        \Magento\Framework\Profiler::start('INSTANT_SEARCH::PRODUCT::GET ITEMS');
        $result = [];

        if ($this->configurationHelper->isEnabled($this->getType())) {
            foreach ($this->getQueryResponse()->getIterator() as $document) {
                $result[] = $this->itemFactory->create(
                    [
                        'source' => $document->getSource(),
                        'type'   => $this->getType(),
                    ]
                );
            }
        }

        \Magento\Framework\Profiler::stop('INSTANT_SEARCH::PRODUCT::GET ITEMS');

        return $result;
    }

    /**
     * Get query response.
     *
     * @return \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponse
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQueryResponse()
    {
        if (null === $this->queryResponse) {
            $request             = $this->prepareRequest();
            $this->queryResponse = $this->searchEngine->search($request);
        }

        return $this->queryResponse;
    }

    /**
     * Prepare the search request before it will be executed.
     *
     * @return RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareRequest()
    {
        // Store id and request name.
        $storeId           = $this->getStoreId();
        $searchRequestName = $this->searchRequestName;
        $query             = $this->getQueryText();

        return $this->requestBuilder->create(
            $storeId,
            $searchRequestName,
            0,
            $this->getResultsPageSize(),
            $query
        );
    }

    /**
     * Retrieve number of products to display in autocomplete results
     *
     * @return int
     */
    private function getResultsPageSize()
    {
        return $this->configurationHelper->getMaxSize($this->getType());
    }

    /**
     * Get current store ID.
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * List of search terms suggested by the search terms data provider.
     *
     * @return array
     */
    private function getQueryText()
    {
        $terms = array_map(
            function (TermItem $termItem) {
                return $termItem->getTitle();
            },
            $this->termDataProvider->getItems()
        );

        if (empty($terms)) {
            $terms = [$this->getQueryStringProvider()->get()];
        }

        return $terms;
    }

    /**
     * @return QueryStringProvider
     */
    private function getQueryStringProvider()
    {
        return $this->queryStringProviderFactory->create();
    }
}
