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

namespace Smile\ElasticsuiteRecommenderGraphQl\Model\Resolver;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Visitor\Service;

/**
 * Resolver for Visitor Products
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommenderGraphQl
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class VisitorProducts implements ResolverInterface
{
    /**
     * @var VisitorProducts\FieldSelector
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
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Search\ContextInterface
     */
    private $searchContext;

    /**
     * VisitorProducts constructor.
     *
     * @param Service                       $service               Recommender Service
     * @param VisitorProducts\FieldSelector $productFieldsSelector Product Fields Selector
     * @param SearchCriteriaBuilder         $searchCriteriaBuilder Search Criteria Builder
     * @param ProductDataProvider           $productDataProvider   Product Data Provider
     * @param CategoryRepositoryInterface   $categoryRepository    Category Repository
     * @param ContextInterface              $searchContext         Search Context
     */
    public function __construct(
        Service $service,
        VisitorProducts\FieldSelector $productFieldsSelector,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductDataProvider $productDataProvider,
        CategoryRepositoryInterface $categoryRepository,
        ContextInterface $searchContext
    ) {
        $this->service               = $service;
        $this->productFieldsSelector = $productFieldsSelector;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productDataProvider   = $productDataProvider;
        $this->categoryRepository    = $categoryRepository;
        $this->searchContext         = $searchContext;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateArgs($args);

        $categories     = $this->getCategories($args);
        $productIds     = $this->service->getRecommendedProductIds(null, $args['pageSize'] ?? null, $categories, $args['visitorId']);
        $loadAttributes = $this->productFieldsSelector->getProductsFieldSelection($info);

        $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');

        $products = $this->productDataProvider->getList(
            $this->searchCriteriaBuilder->create(),
            $loadAttributes,
            false,
            true
        );

        $productArray = [];

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products->getItems() as $product) {
            $productArray[$product->getId()]          = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        // Ensure products are sorted accordingly to the service result, which is in correct order.
        uksort($productArray, function ($key1, $key2) use ($productIds) {
            return (array_search($key1, $productIds) > array_search($key2, $productIds));
        });

        return ['items' => $productArray];
    }

    /**
     * Get current category from args.
     *
     * @param array $args GraphQl Args
     *
     * @return array|string
     */
    private function getCategories($args)
    {
        $categories = [];

        try {
            $category   = $this->categoryRepository->get($args['categoryId']);
            $categories = [$args['categoryId']];

            if (!$this->searchContext->getCurrentCategory()) {
                $this->searchContext->setCurrentCategory($category);
            }

            $childrenIds = $category->getAllChildren(true);
            if (null !== $childrenIds) {
                $categories = array_merge($categories, $childrenIds);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            ;
        }

        return $categories;
    }

    /**
     * Validate GraphQL query arguments and throw exception if needed.
     *
     * @param array $args GraphQL query arguments
     *
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args)
    {
        if (!isset($args['categoryId']) || ((int) $args['categoryId'] === 0)) {
            throw new GraphQlInputException(
                __("'categoryId' input argument is required.")
            );
        }
        if (!isset($args['visitorId']) || ((string) $args['visitorId'] === '')) {
            throw new GraphQlInputException(
                __("'visitorId' input argument is required.")
            );
        }
    }
}
