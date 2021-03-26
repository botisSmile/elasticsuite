<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Model\Term\Merchandiser;

use Magento\Search\Model\QueryInterface as SearchQueryInterface;
use Smile\ElasticsuiteMerchandisingGauge\Model\Merchandiser\AbstractMeasure as MerchandiserMeasure;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory as ProductCollectionFactory;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteMerchandisingGauge\Model\DimensionProvider;

/**
 * Search term merchandiser measure
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class Measure extends MerchandiserMeasure
{
    /**
     * @var QueryInterface
     */
    private $searchQuery;

    /**
     * Measure constructor.
     *
     * @param SearchQueryInterface     $searchQuery        Search query.
     * @param ProductCollectionFactory $collectionFactory  Product collection factory.
     * @param QueryFactory             $queryFactory       Query factory.
     * @param ContextInterface         $searchContext      Search context.
     * @param DimensionProvider        $dimensionProvider  Merchandising dimension provider.
     * @param string                   $preferredDimension Preferred dimension identifier.
     * @param int                      $sampleSize         First products sample size to take into account.
     * @param int                      $pageSize           Full preview size.
     * @param array                    $visibility         Products visibility.
     */
    public function __construct(
        SearchQueryInterface $searchQuery,
        ProductCollectionFactory $collectionFactory,
        QueryFactory $queryFactory,
        ContextInterface $searchContext,
        DimensionProvider $dimensionProvider,
        $preferredDimension = null,
        $sampleSize = 10,
        $pageSize = 10,
        $visibility = [Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH]
    ) {
        parent::__construct(
            $collectionFactory,
            $queryFactory,
            $searchContext,
            $dimensionProvider,
            $preferredDimension,
            $sampleSize,
            $pageSize,
            $visibility
        );
        $this->searchQuery = $searchQuery;
        $this->searchContext->setStoreId($this->searchQuery->getStoreId());
        $this->searchContext->setCurrentSearchQuery($this->searchQuery);
    }

    /**
     * Return the list of blacklisted product ids.
     *
     * @return array
     */
    protected function getBlacklistedProductIds()
    {
        return $this->searchQuery->getBlacklistedProductIds() ?? [];
    }

    /**
     * Return the list of manually sorted product ids.
     *
     * @return array
     */
    protected function getSortedProductIds()
    {
        return $this->searchQuery->getSortedProductIds() ?? [];
    }

    /**
     * Prepare the product collection.
     *
     * @param Collection $collection Fulltext product collection
     *
     * @return Collection
     */
    protected function prepareBaseProductCollection(Collection $collection)
    {
        $collection->setVisibility($this->visibility);
        $collection->setSearchQuery($this->searchQuery->getQueryText());

        return $collection;
    }
}
