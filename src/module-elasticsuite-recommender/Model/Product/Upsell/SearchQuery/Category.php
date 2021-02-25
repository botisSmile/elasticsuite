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
use Smile\ElasticsuiteRecommender\Model\CoOccurrence;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\Nested;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Helper\Data as DataHelper;

/**
 * Upsell search query category clause builder
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Category implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var CoOccurrence
     */
    private $coOccurrence;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory Query factory.
     * @param CoOccurrence $coOccurrence Co-occurrence finder.
     * @param DataHelper   $helper       Data helper.
     */
    public function __construct(QueryFactory $queryFactory, CoOccurrence $coOccurrence, DataHelper $helper)
    {
        $this->queryFactory = $queryFactory;
        $this->coOccurrence = $coOccurrence;
        $this->helper       = $helper;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        if ($categoryIds = $this->getCategories($product)) {
            $categoryQuery = $this->queryFactory->create(
                QueryInterface::TYPE_TERMS,
                ['field' => 'category.category_id', 'values' => $categoryIds]
            );

            $query = $this->queryFactory->create(
                QueryInterface::TYPE_NESTED,
                ['scoreMode' => Nested::SCORE_MODE_SUM, 'path' => 'category', 'query' => $categoryQuery]
            );
        }

        return $query;
    }

    /**
     * Get viewed categories when a given product has been viewed
     *
     * @param ProductInterface $product Product.
     *
     * @return array Categories Ids
     */
    private function getCategories(ProductInterface $product)
    {
        $categoryIds = [];

        if ($this->helper->useCategoryViewsCoOccurrencesForUpsells()) {
            $categoryIds = $this->coOccurrence->getCoOccurrences(
                'product_view',
                $product->getId(),
                $product->getStoreId(),
                'category_view'
            );
        }

        if (empty($categoryIds)) {
            $categoryIds = $product->getCategoryIds();
        }

        return array_diff($categoryIds, [$product->getStore()->getRootCategoryId()]);
    }
}
