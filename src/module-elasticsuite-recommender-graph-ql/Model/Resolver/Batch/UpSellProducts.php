<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommenderGraphQl
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommenderGraphQl\Model\Resolver\Batch;

use Magento\Catalog\Model\Product\Link;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;

/**
 * Resolver for Upsell Products
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommenderGraphQl
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class UpSellProducts extends AbstractLinkedProducts implements BatchResolverInterface
{
    /**
     * Up Sell Products constructor.
     *
     * @param \Smile\ElasticsuiteRecommender\Model\Product\Matcher                 $model                 Recommender model
     * @param \Smile\ElasticsuiteRecommender\Helper\Data                           $helper                Helper
     * @param \Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector $productFieldsSelector Field Selector
     * @param SearchCriteriaBuilder                                                $searchCriteriaBuilder Search Criteria Builder
     * @param ProductDataProvider                                                  $productDataProvider   Product Data Provider
     */
    public function __construct(
        \Smile\ElasticsuiteRecommender\Model\Product\Matcher $model,
        \Smile\ElasticsuiteRecommender\Helper\Data $helper,
        ProductFieldsSelector $productFieldsSelector,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductDataProvider $productDataProvider
    ) {
        parent::__construct($model, $helper, $productFieldsSelector, $searchCriteriaBuilder, $productDataProvider);
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return 'upsell-rule';
    }

    /**
     * {@inheritDoc}
     */
    protected function getNode(): string
    {
        return 'upsell_products';
    }
}