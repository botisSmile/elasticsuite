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

class RelatedProducts implements SearchQueryBuilderInterface
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
     * @param QueryFactory $queryFactory
     */
    public function __construct(QueryFactory $queryFactory, Coocurence $coocurence, UpsellConfig $config)
    {
        $this->queryFactory = $queryFactory;
        $this->coocurence   = $coocurence;
        $this->config       = $config;
    }

    public function getSearchQuery(ProductInterface $product)
    {
        $similarityQueryFields = $this->config->getSimilarityFields($product->getStoreId());
        $query = false;

        if ($productIds = $this->getProducts($product)) {
            $likes = [['_id' => $product->getId()]];

            foreach ($productIds as $relatedProduct) {
                 $likes[] = ['_id' => $relatedProduct];
             }

            $query = $this->queryFactory->create(
                QueryInterface::TYPE_MORELIKETHIS,
                ['fields'=> $similarityQueryFields, 'like' => $likes, 'includeOriginalDocs' => true]
            );
        }

        return $query;
    }

    private function getProducts(ProductInterface $product)
    {
        return $this->coocurence->getCoocurences('product_view', $product->getId(), $product->getStoreId(), 'product_view');
    }
}
