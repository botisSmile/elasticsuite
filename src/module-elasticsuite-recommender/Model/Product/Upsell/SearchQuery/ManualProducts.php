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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Upsell\Config as UpsellConfig;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\CoOccurrence;

/**
 * Upsell search query manual upsell recommendations clause builder
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class ManualProducts implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var UpsellConfig
     */
    private $config;

    /**
     * @var CoOccurrence
     */
    private $coOccurrence;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory Query factory.
     * @param CoOccurrence $coOccurrence Co-occurrence finder.
     * @param UpsellConfig $config       Upsell config model.
     */
    public function __construct(QueryFactory $queryFactory, CoOccurrence $coOccurrence, UpsellConfig $config)
    {
        $this->queryFactory = $queryFactory;
        $this->coOccurrence = $coOccurrence;
        $this->config       = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        if ($productIds = $this->getProducts($product)) {
            $queryParams = [
                'includeOriginalDocs' => true,
                'boost'               => 100,
                'fields'              => $this->config->getSimilarityFields($product->getStoreId()),
                'like'                => [],
            ];

            $queryParams['like'][] = ['_id' => $product->getId()];
            foreach ($productIds as $relatedProduct) {
                $queryParams['like'][] = ['_id' => $relatedProduct];
            }

            $query = $this->queryFactory->create(QueryInterface::TYPE_MORELIKETHIS, $queryParams);
        }

        return $query;
    }

    /**
     * Return the manually configured upsell products ids for a given product
     *
     * @param ProductInterface $product Product
     *
     * @return array
     */
    private function getProducts(ProductInterface $product)
    {
        return $product->getUpsellProductIds();
    }
}