<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Upsell\SearchQuery\RelatedProducts as SimilarProductQueryProvider;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\CollectionProvider;

/**
 * Find co-occurrences across event into the session data.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Similarity
{
    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Upsell\SearchQuery\RelatedProducts
     */
    private $queryProvider;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Similarity constructor.
     *
     * @param SimilarProductQueryProvider $queryProvider      More Like This query provider
     * @param CollectionProvider          $collectionProvider Collection Provider
     */
    public function __construct(SimilarProductQueryProvider $queryProvider, CollectionProvider $collectionProvider)
    {
        $this->queryProvider      = $queryProvider;
        $this->collectionProvider = $collectionProvider;
    }

    /**
     * Get products similar to a given product.
     *
     * @param ProductInterface $product The product to get similar products from.
     * @param integer          $size    Number of products to find.
     *
     * @return string[]
     */
    public function getSimilarProductIds(ProductInterface $product, $size = 10)
    {
        $cacheKey          = md5(json_encode(func_get_args()));
        $productCollection = $this->collectionProvider->getCollection();

        if (!isset($this->cache[$cacheKey])) {
            $query = $this->queryProvider->getSearchQuery($product);
            $productCollection->addSearchFilter($query)->setPageSize($size);
            $productIds = $productCollection->load()->getAllIds();

            $this->cache[$cacheKey] = $productIds;
        }

        return $this->cache[$cacheKey];
    }
}
