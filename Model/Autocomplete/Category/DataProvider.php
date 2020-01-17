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
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\Item as TermItem;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalog\Helper\Autocomplete as ConfigurationHelper;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Category\Fulltext\CollectionFactory as CategoryCollectionFactory;
use Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider as TermDataProvider;
use Smile\ElasticsuiteCore\Search\Request\Builder as RequestBuilder;
use Smile\ElasticsuiteCore\Search\RequestInterface;
use Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product\ItemFactory;

/**
 * Catalog category autocomplete data provider.
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
    const AUTOCOMPLETE_TYPE = "category";

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
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param ItemFactory           $itemFactory           Suggest item factory.
     * @param ConfigurationHelper   $configurationHelper   Autocomplete configuration helper.
     * @param RequestBuilder        $requestBuilder        Search Request Builder.
     * @param SearchEngineInterface $searchEngineInterface Search Engine Interface.
     * @param TermDataProvider      $termDataProvider      Terms Data Provider.
     * @param QueryFactory          $queryFactory          Search Query Factory.
     * @param StoreManagerInterface $storeManager          Store Manager Interface.
     * @param string                $type                  Autocomplete provider type.
     * @param string                $searchRequestName     Search Request Name.
     */
    public function __construct(
        ItemFactory $itemFactory,
        ConfigurationHelper $configurationHelper,
        RequestBuilder $requestBuilder,
        SearchEngineInterface $searchEngineInterface,
        TermDataProvider $termDataProvider,
        QueryFactory $queryFactory,
        StoreManagerInterface $storeManager,
        $type = self::AUTOCOMPLETE_TYPE,
        $searchRequestName = 'category_search_container'
    ) {
        $this->itemFactory         = $itemFactory;
        $this->configurationHelper = $configurationHelper;
        $this->requestBuilder      = $requestBuilder;
        $this->searchEngine        = $searchEngineInterface;
        $this->termDataProvider    = $termDataProvider;
        $this->queryFactory        = $queryFactory;
        $this->storeManager        = $storeManager;
        $this->type                = $type;
        $this->searchRequestName   = $searchRequestName;
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
     */
    public function getItems()
    {
        \Magento\Framework\Profiler::start('INSTANT_SEARCH::CATEGORY::GET ITEMS');
        $result = [];

        if ($this->configurationHelper->isEnabled($this->getType())) {
            $request       = $this->prepareRequest();
            $queryResponse = $this->searchEngine->search($request);

            foreach ($queryResponse->getIterator() as $document) {
                $result[] = $this->itemFactory->create(
                    [
                        'source' => $document->getSource(),
                        'type'   => $this->getType(),
                    ]
                );
            }
        }
        \Magento\Framework\Profiler::stop('INSTANT_SEARCH::CATEGORY::GET ITEMS');
        return $result;
    }

    /**
     * Prepare the search request before it will be executed.
     *
     * @return RequestInterface
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
            $terms = [$this->queryFactory->get()->getQueryText()];
        }

        return $terms;
    }
}
