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

namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell\SearchQuery;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Helper\Data;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Upsell search query higher price clause builder
 *
 * @category Smile
 * @package  Smile\Elasticsuite
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class HigherPrice implements SearchQueryBuilderInterface
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
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Constructor.
     *
     * @param QueryFactory    $queryFactory    Query factory.
     * @param Data            $helper          Data helper.
     * @param ProductHelper   $productHelper   Product helper.
     * @param CustomerSession $customerSession Customer session.
     */
    public function __construct(
        QueryFactory $queryFactory,
        Data $helper,
        ProductHelper $productHelper,
        CustomerSession $customerSession
    ) {
        $this->queryFactory     = $queryFactory;
        $this->helper           = $helper;
        $this->productHelper    = $productHelper;
        $this->customerSession  = $customerSession;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        if ($this->helper->isForceHigherPriceForUpsells()) {
            $bounds = ['gte' => $this->productHelper->getFinalPrice($product)];
            $query = $this->getPriceRangeQuery($bounds);
        }

        return $query;
    }

    /**
     * Create a query filter for price, according to current customer group Id.
     *
     * @param array $bounds The price bounds to apply
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getPriceRangeQuery($bounds)
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $priceQuery = $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            [
                'path'  => 'price',
                'query' => $this->queryFactory->create(
                    QueryInterface::TYPE_BOOL,
                    [
                        'must' => [
                            $this->queryFactory->create(
                                QueryInterface::TYPE_TERM,
                                ['field' => 'price.customer_group_id', 'value' => $customerGroupId]
                            ),
                            $this->queryFactory->create(
                                QueryInterface::TYPE_RANGE,
                                ['field' => 'price.price', 'bounds' => $bounds]
                            ),
                        ],
                    ]
                ),
            ]
        );

        return $priceQuery;
    }
}
