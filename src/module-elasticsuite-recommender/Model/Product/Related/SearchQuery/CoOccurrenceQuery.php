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
namespace Smile\ElasticsuiteRecommender\Model\Product\Related\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Upsell\Config as UpsellConfig;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\CoOccurrence;

/**
 * Generic event co-occurrence search query builder.
 * Helps recommend products that are similar to products which have also been - depending on the selected "co-occurrence field" -
 * viewed or added to cart or bought when the product we want recommendations for was viewed or added to cart or bought.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class CoOccurrenceQuery implements SearchQueryBuilderInterface
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
     * @var string
     */
    private $coOccurrenceField;

    /**
     * @var integer
     */
    private $boost;

    /**
     * @var string
     */
    private $minimumShouldMatch;

    /**
     * CoOccurrenceQuery constructor.
     *
     * @param QueryFactory $queryFactory       Query factory.
     * @param CoOccurrence $coOccurrence       Co-occurrence finder.
     * @param UpsellConfig $config             Upsell config model.
     * @param string       $coOccurrenceField  Co-occurrence field.
     * @param int          $boost              Query boost.
     * @param string       $minimumShouldMatch Minimum should match.
     */
    public function __construct(
        QueryFactory $queryFactory,
        CoOccurrence $coOccurrence,
        UpsellConfig $config,
        $coOccurrenceField,
        $boost = 1,
        $minimumShouldMatch = "30%"
    ) {
        $this->queryFactory       = $queryFactory;
        $this->coOccurrence       = $coOccurrence;
        $this->config             = $config;
        $this->coOccurrenceField  = $coOccurrenceField;
        $this->boost              = $boost;
        $this->minimumShouldMatch = $minimumShouldMatch;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query      = false;
        $productIds = $this->getProducts($product);

        if (!empty($productIds)) {
            $queryParams = [
                'includeOriginalDocs' => true,
                'minimumShouldMatch'  => $this->minimumShouldMatch,
                'boost'               => $this->boost,
                'fields'              => $this->config->getSimilarityFields($product->getStoreId()),
                'like'                => [],
            ];

            foreach ($productIds as $relatedProduct) {
                $queryParams['like'][] = ['_id' => $relatedProduct];
            }

            $query = $this->queryFactory->create(QueryInterface::TYPE_MORELIKETHIS, $queryParams);
        }

        return $query;
    }

    /**
     * Get co-occurrences of a product according to the co-occurrence field.
     * For instance if the co-occurrence field is "product_cart", it will return all products also added to cart
     * when this product was.
     *
     * @param ProductInterface $product Product to get co-occurrences for.
     *
     * @return array
     */
    private function getProducts(ProductInterface $product)
    {
        $productId      = $product->getId();
        $storeId        = $product->getStoreId();
        $coOccurrences  = $this->coOccurrence->getCoOccurrences(
            $this->coOccurrenceField,
            $productId,
            $storeId,
            $this->coOccurrenceField
        );

        return array_diff(array_map('intval', $coOccurrences), [$productId]);
    }
}
