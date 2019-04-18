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
        $similarityQueryFields = $this->config->getSimilarityFields($product->getStoreId());

        if ($productIds = $this->getProducts($product)) {
            $likes = [['_id' => $product->getId()]];

            foreach ($productIds as $relatedProduct) {
                 $likes[] = ['_id' => $relatedProduct];
            }

            $query = $this->queryFactory->create(
                QueryInterface::TYPE_MORELIKETHIS,
                ['fields' => $similarityQueryFields, 'like' => $likes, 'includeOriginalDocs' => true, 'boost' => 100]
            );
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
