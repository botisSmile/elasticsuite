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

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;

/**
 * Abstract Resolver for linked products
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommenderGraphQl
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
abstract class AbstractLinkedProducts
{
    /**
     * @var \Smile\ElasticsuiteRecommender\Helper\Data
     */
    private $helper;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher
     */
    private $model;

    /**
     * @var \Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * RelatedProducts constructor.
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
        $this->helper                = $helper;
        $this->model                 = $model;
        $this->productFieldsSelector = $productFieldsSelector;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productDataProvider   = $productDataProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $products */
        $products = [];
        $fields   = [];
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            if (empty($request->getValue()['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $products[] = $request->getValue()['model'];
            $fields[]   = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $this->getNode());
        }

        $fields   = array_unique(array_merge(...$fields));
        $related  = $this->findRelations($products, $fields);
        $response = new BatchResponse();

        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $request->getValue()['model'];
            $result  = [];
            if (array_key_exists($product->getId(), $related)) {
                $result = array_map(
                    function ($relatedProduct) {
                        $data = $relatedProduct->getData();
                        $data['model'] = $relatedProduct;

                        return $data;
                    },
                    $related[$product->getId()]
                );
            }
            $response->addResponse($request, $result);
        }

        return $response;
    }

    /**
     * Find related products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products       The products
     * @param string[]                                     $loadAttributes The attributes to load
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[][] Products
     */
    protected function findRelations(array $products, array $loadAttributes): array
    {
        $relations = [];

        foreach ($products as $product) {
            $items = $this->model->getItems($product, $this->getBehavior(), $this->getPositionLimit());
            foreach ($items as $item) {
                $relations[$product->getId()][] = $item->getId();
            }
        }

        if (!$relations) {
            return [];
        }

        $relatedIds = array_values($relations);
        $relatedIds = array_unique(array_merge(...$relatedIds));

        $this->searchCriteriaBuilder->addFilter('entity_id', $relatedIds, 'in');
        $relatedSearchResult = $this->productDataProvider->getList(
            $this->searchCriteriaBuilder->create(),
            $loadAttributes,
            false,
            true
        );

        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $relatedProducts */
        $relatedProducts = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $item */
        foreach ($relatedSearchResult->getItems() as $item) {
            $relatedProducts[$item->getId()] = $item;
        }

        $relationsData = [];
        foreach ($relations as $productId => $relatedIds) {
            $relationsData[$productId] = array_map(
                function ($pid) use ($relatedProducts) {
                    return $relatedProducts[$pid];
                },
                $relatedIds
            );
        }

        return $relationsData;
    }

    /**
     * Type of linked products to be resolved.
     *
     * @return int
     */
    abstract protected function getType(): string;

    /**
     * Number of recommendations to load.
     *
     * @return number
     */
    private function getPositionLimit()
    {
        return $this->helper->getPositionLimit($this->getType());
    }

    /**
     * Recommendations loading behavior.
     *
     * @return int
     */
    private function getBehavior()
    {
        return $this->helper->getBehavior($this->getType());
    }
}
