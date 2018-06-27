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

class Matcher
{
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
     * Constructor.
     *
     * @param Matcher\CollectionProvider          $collectionProvider
     * @param Matcher\SearchQueryBuilderInterface $searchQueryBuilder
     * @param Matcher\ItemProviderInterface       $itemProvider
     */
    public function __construct(
        Matcher\CollectionProvider $collectionProvider,
        Matcher\SearchQueryBuilderInterface $searchQueryBuilder,
        Matcher\ItemProviderInterface $itemProvider = null
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->searchQueryBuilder = $searchQueryBuilder;
        $this->itemProvider       = $itemProvider;
    }

    /**
     * Load recommendations.
     *
     * @param ProductInterface $product Source product.
     * @param number           $size    Number of recommendations to load.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getItems(ProductInterface $product, $size = 6)
    {
        $items = [];

        if ($this->itemProvider !== null) {
            $items = $this->itemProvider->getItems($product);
        }

        $automaticSize = $size - count($items);

        if ($automaticSize > 0) {
            $productCollection = $this->collectionProvider->getCollection();
            $productCollection->setPageSize($automaticSize);
            $searchQuery = $this->getSearchQuery($product);

            if ($searchQuery) {
                $productCollection->addSearchFilter($this->getSearchQuery($product));
                $items = array_merge($items, $productCollection->getItems());
            }
        }

        return $items;
    }

    /**
     * Build the search query to load recommendations.
     *
     * @param ProductInterface $product Source product.
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getSearchQuery(ProductInterface $product)
    {
        return $this->searchQueryBuilder->getSearchQuery($product);
    }
}
