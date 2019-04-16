<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Related\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Helper\Data;
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * Composite product types exclusion filter
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class CompositeProductTypeFilter implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ProductType
     */
    private $productType;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory Query factory.
     * @param Data         $helper       Data helper.
     * @param ProductType  $productType  Product type list.
     */
    public function __construct(QueryFactory $queryFactory, Data $helper, ProductType $productType)
    {
        $this->queryFactory = $queryFactory;
        $this->helper       = $helper;
        $this->productType  = $productType;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        if ($this->helper->isExcludingCompositeForRelated()) {
            $compositeTypes = $this->productType->getCompositeTypes();
            if (!empty($compositeTypes)) {
                $query = $this->queryFactory->create(QueryInterface::TYPE_TERMS, ['field' => 'type_id', 'values' => $compositeTypes]);
            }
        }

        return $query;
    }
}
