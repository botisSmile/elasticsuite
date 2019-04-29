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
namespace Smile\ElasticsuiteRecommender\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Helper\Data as DataHelper;

/**
 * Manual and automatic recommendations loader.
 * If instantiated with a provider of manual recommended items,
 * it will only complement if need be that items list with automatic recommendations.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Matcher
{
    /**
     * Manual and automatic recommendations behavior
     * @var int
     */
    const BOTH_SELECTED_AND_EVENT_BASED = 0;

    /**
     * Manual recommendations only behavior
     * @var int
     */
    const SELECTED_ONLY = 1;

    /**
     * Automatic recommendations only behavior
     * @var int
     */
    const EVENT_BASED_ONLY = 2;

    /**
     * @var Matcher\CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var Matcher\SearchQueryBuilderInterface
     */
    private $searchQueryBuilder;

    /**
     * @var Matcher\ItemProviderInterface
     */
    private $itemProvider;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param Matcher\CollectionProvider          $collectionProvider Collection provider.
     * @param Matcher\SearchQueryBuilderInterface $searchQueryBuilder Search query builder.
     * @param DataHelper                          $helper             Data helper.
     * @param Matcher\ItemProviderInterface       $itemProvider       Item provider.
     */
    public function __construct(
        Matcher\CollectionProvider $collectionProvider,
        Matcher\SearchQueryBuilderInterface $searchQueryBuilder,
        DataHelper $helper,
        Matcher\ItemProviderInterface $itemProvider = null
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->searchQueryBuilder = $searchQueryBuilder;
        $this->helper             = $helper;
        $this->itemProvider       = $itemProvider;
    }

    /**
     * Load recommendations.
     *
     * @param ProductInterface $product  Source product.
     * @param int              $behavior Loading behavior for recommendations (manual, event based or both).
     * @param int              $size     Number of recommendations to load.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getItems(ProductInterface $product, $behavior, $size = 6)
    {
        $items = [];

        if (in_array($behavior, [self::SELECTED_ONLY, self::BOTH_SELECTED_AND_EVENT_BASED])) {
            if ($this->itemProvider !== null) {
                $items = $this->itemProvider->getItems($product, $size);
            }
        }

        if ($behavior == self::SELECTED_ONLY) {
            return $items;
        }

        $automaticSize = $size - count($items);

        if ($automaticSize > 0) {
            $productCollection = $this->collectionProvider->getCollection();
            $productCollection->setPageSize($automaticSize);
            $searchQuery = $this->getSearchQuery($product);

            if ($searchQuery) {
                $productCollection->addSearchFilter($searchQuery);
                $items = array_merge($items, $productCollection->getItems());
            }
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
