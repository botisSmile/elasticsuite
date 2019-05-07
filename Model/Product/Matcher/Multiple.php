<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Helper\Data as DataHelper;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Class Multiple
 * TODO ribay@smile.fr : merge with \Smile\ElasticsuiteRecommender\Model\Product\Matcher ?
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Multiple
{
    /**
     * @var CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var SearchQueryBuilderInterface
     */
    private $searchQueryBuilder;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param CollectionProvider          $collectionProvider Collection provider.
     * @param SearchQueryBuilderInterface $searchQueryBuilder Search query builder.
     * @param QueryFactory                $queryFactory       Query factory.
     * @param DataHelper                  $helper             Data helper.
     */
    public function __construct(
        CollectionProvider $collectionProvider,
        SearchQueryBuilderInterface $searchQueryBuilder,
        QueryFactory $queryFactory,
        DataHelper $helper
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->searchQueryBuilder = $searchQueryBuilder;
        $this->queryFactory       = $queryFactory;
        $this->helper             = $helper;
    }

    /**
     * Load recommendations.
     *
     * @param ProductInterface[] $products Source products.
     * @param int                $size     Number of recommendations to load.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getItems(array $products, $size = 6)
    {
        $items = [];

        $searchQueries = [];
        foreach ($products as $product) {
            if ($searchQuery = $this->getSearchQuery($product)) {
                $searchQueries[] = $searchQuery;
            }
        }

        if (!empty($searchQueries)) {
            $queryClauses   = ['should' => $searchQueries, 'minimum_should_match' => 1];
            $searchQuery    = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $queryClauses);

            $productCollection = $this->collectionProvider->getCollection();
            $productCollection->setPageSize($size);
            $productCollection->addSearchFilter($searchQuery);
            $items = $productCollection->getItems();
        }

        return $items;
    }

    /**
     * Build the search query to load recommendations.
     *
     * @param ProductInterface $product Source product to get recommendations for.
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getSearchQuery(ProductInterface $product)
    {
        return $this->searchQueryBuilder->getSearchQuery($product);
    }
}
