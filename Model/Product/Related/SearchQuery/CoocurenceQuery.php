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
use Smile\ElasticsuiteRecommender\Model\Coocurence;

class CoocurenceQuery implements SearchQueryBuilderInterface
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
     * @var string
     */
    private $coocurenceField;

    /**
     * @var integer
     */
    private $boost;

    /**
     * @var string
     */
    private $minimumShouldMatch;

    public function __construct(
        QueryFactory $queryFactory,
        Coocurence $coocurence,
        UpsellConfig $config,
        $coocurenceField,
        $boost = 1,
        $minimumShouldMatch = "30%"
    ) {
        $this->queryFactory       = $queryFactory;
        $this->coocurence         = $coocurence;
        $this->config             = $config;
        $this->coocurenceField    = $coocurenceField;
        $this->boost              = $boost;
        $this->minimumShouldMatch = $minimumShouldMatch;
    }

    public function getSearchQuery(ProductInterface $product)
    {
        $query      = false;
        $productIds = $this->getProducts($product);

        if (!empty($productIds)) {
            $queryParams =  [
                'fields'              => $this->config->getSimilarityFields($product->getStoreId()),
                'includeOriginalDocs' => true,
                'minimumShouldMatch'  => $this->minimumShouldMatch,
                'boost'               => $this->boost,
            ];

            foreach ($productIds as $relatedProduct) {
                $queryParams['like'][] = ['_id' => $relatedProduct];
            }

            $query = $this->queryFactory->create(QueryInterface::TYPE_MORELIKETHIS, $queryParams);
        }

        return $query;
    }

    private function getProducts(ProductInterface $product)
    {
        $productId   = $product->getId();
        $storeId     = $product->getStoreId();
        $coocurences = $this->coocurence->getCoocurences($this->coocurenceField, $productId, $storeId, $this->coocurenceField);

        return array_diff(array_map('intval', $coocurences), [$productId]);
    }
}
