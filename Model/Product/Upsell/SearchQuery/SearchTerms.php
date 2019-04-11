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
namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Upsell\Config as UpsellConfig;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Coocurence;

/**
 * Upsell search query fulltext search co-occurences based clause builder.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class SearchTerms implements SearchQueryBuilderInterface
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
     * @var Coocurence
     */
    private $coocurence;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory Query factory.
     * @param Coocurence   $coocurence   Co-occurence finder.
     * @param UpsellConfig $config       Upsell config model.
     */
    public function __construct(QueryFactory $queryFactory, Coocurence $coocurence, UpsellConfig $config)
    {
        $this->queryFactory = $queryFactory;
        $this->coocurence   = $coocurence;
        $this->config       = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        $subQueries = $this->getSubQueries($product);

        if (!empty($subQueries)) {
            $query = $this->queryFactory->create(QueryInterface::TYPE_BOOL, ['should' => $subQueries]);
        }

        return $query;
    }

    /**
     * Return an array of fulltext search queries the upsell products should match as the provided product supposedly did.
     *
     * @param ProductInterface $product Product
     *
     * @return array
     */
    private function getSubQueries(ProductInterface $product)
    {
        $queries     = [];
        $queryFields = $this->config->getWeightedSearchFields($product->getStoreId());

        if ($searchTerms = $this->getSearches($product)) {
            foreach ($searchTerms as $searchTerm) {
                $queries[] = $this->queryFactory->create(
                    QueryInterface::TYPE_MULTIMATCH,
                    ['fields' => $queryFields, 'queryText' => $searchTerm]
                );
            }
        }

        return $queries;
    }

    /**
     * Get search terms searched for when the given product has been viewed
     *
     * @param ProductInterface $product Product
     *
     * @return string[]
     */
    private function getSearches(ProductInterface $product)
    {
        return $this->coocurence->getCoocurences('product_view', $product->getId(), $product->getStoreId(), 'search_query');
    }
}
