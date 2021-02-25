<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Ui\Product\Visitor\Listing;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Store\Model\StoreManager;
use Magento\Catalog\Api\ProductRenderListInterface;
use Magento\Framework\EntityManager\HydratorInterface;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Visitor\Service;
use \Magento\Framework\Registry;

/**
 * Data Provider for product listing blocks of the recommender.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider
{
    /**
     * @var \Magento\Catalog\Api\ProductRenderListInterface
     */
    private $productRenderList;

    /**
     * @var \Magento\Framework\EntityManager\HydratorInterface
     */
    private $hydrator;

    /**
     * @var ContextInterface
     */
    private $searchContext;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Visitor\Service
     */
    private $service;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param string                      $name                  Data Provider Name
     * @param Reporting                   $reporting             Reporting
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder Search Criteria Builder
     * @param RequestInterface            $request               Request
     * @param FilterBuilder               $filterBuilder         Filter Builder
     * @param StoreManager                $storeManager          Store Manager
     * @param ProductRenderListInterface  $productRenderList     Product Render List
     * @param HydratorInterface           $hydrator              Product Hydrator
     * @param ContextInterface            $context               Search Context
     * @param Service                     $service               Visitor Recommendations Service
     * @param CategoryRepositoryInterface $categoryRepository    Category Repository
     * @param Registry                    $registry              Registry
     * @param array                       $meta                  UI Component Meta
     * @param array                       $data                  UI Component Data
     */
    public function __construct(
        $name,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        StoreManager $storeManager,
        ProductRenderListInterface $productRenderList,
        HydratorInterface $hydrator,
        ContextInterface $context,
        Service $service,
        CategoryRepositoryInterface $categoryRepository,
        Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $storeManager,
            $meta,
            $data
        );

        $this->name               = $name;
        $this->productRenderList  = $productRenderList;
        $this->hydrator           = $hydrator;
        $this->searchContext      = $context;
        $this->service            = $service;
        $this->categoryRepository = $categoryRepository;
        $this->registry           = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = parent::getData();

        $productIds = $this->getRecommendedProductIds();

        $productIdFilter = $this->filterBuilder->setField('entity_id')
            ->setConditionType('in')
            ->setValue($productIds)
            ->create();

        $this->searchCriteriaBuilder->addFilter($productIdFilter);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $renderSearchResults = $this->productRenderList->getList($searchCriteria, $data['store'], $data['currency']);

        foreach ($renderSearchResults->getItems() as $item) {
            $data['items'][] = $this->hydrator->extract($item);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigData()
    {
        $data = parent::getConfigData();

        if (!isset($data['params']) || !is_array($data['params'])) {
            $data['params'] = [];
        }

        if (!isset($data['params']['category_id'])) {
            $category = $this->getCategory();
            if (null !== $category && $category->getId()) {
                $data['params']['category_id'] = $category->getId();
            }
        }

        return $data;
    }

    /**
     * Get current category
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    private function getCategory()
    {
        $category = $this->searchContext->getCurrentCategory();

        // Category can be null if no search request (or layer) exists on current page.
        if (($category === null) && ($this->registry->registry('current_category') !== null)) {
            $category = $this->registry->registry('current_category');
        }

        return $category;
    }

    /**
     * @return array
     */
    private function getRecommendedProductIds()
    {
        $categoryId = $this->request->getParam('category_id', null);
        $categories = [];

        if ($categoryId) {
            try {
                $category   = $this->categoryRepository->get($categoryId);
                $categories = [$categoryId];

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
        }

        $productIds = $this->service->getRecommendedProductIds(
            null,
            $this->request->getParam('page_size', null),
            $categories
        );

        return $productIds;
    }
}
