<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Ui\Component\Optimizer\Listing;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Category\OptimizerFilter;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;

/**
 * Filter optimizer collection by category processor.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class FilterCollectionByCategory implements OptimizerCollectionProcessorInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ContextInterface
     */
    private $searchContext;

    /**
     * @var OptimizerFilter
     */
    private $optimizerFilter;

    /**
     * DataProvider Constructor.
     *
     * @param RequestInterface            $request            Request
     * @param CategoryRepositoryInterface $categoryRepository Category repository
     * @param ContextInterface            $searchContext      Search context
     * @param OptimizerFilter             $optimizerFilter    Optimizer filter
     */
    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        ContextInterface $searchContext,
        OptimizerFilter $optimizerFilter
    ) {
        $this->request              = $request;
        $this->optimizerFilter      = $optimizerFilter;
        $this->categoryRepository   = $categoryRepository;
        $this->searchContext        = $searchContext;
    }

    /**
     * {@inheritdoc}
     */
    public function process(OptimizerCollection $collection)
    {
        $categoryId = (int) $this->request->getParam('category_id');
        if ($categoryId) {
            $collection->addFieldToFilter('optimizer_id', ['in' => $this->getOptimizerIds($categoryId)]);
        }
    }

    /**
     * Get optimizer ids potentially applied for a category.
     *
     * @param int $categoryId Category Id
     * @return array
     */
    private function getOptimizerIds(int $categoryId): array
    {
        $this->updateSearchContext($categoryId);

        return $this->optimizerFilter->getOptimizerIds();
    }

    /**
     * Update search context.
     *
     * @param int $categoryId Category Id
     * @return void
     */
    private function updateSearchContext(int $categoryId)
    {
        $category = $this->getCategory($categoryId);
        $this->searchContext->setStoreId($this->getStoreId());
        if ($category && $category->getId()) {
            $this->searchContext->setCurrentCategory($category);
        }
    }

    /**
     * Load current category using the request params.
     *
     * @param int $categoryId Category Id
     * @return CategoryInterface|null
     */
    private function getCategory(int $categoryId)
    {
        try {
            return $this->categoryRepository->get($categoryId, $this->getStoreId());
        } catch (NoSuchEntityException $noSuchEntityException) {
            return null;
        }
    }

    /**
     * Return the store id to preview.
     *
     * @return int
     */
    private function getStoreId(): int
    {
        return (int) $this->request->getParam('store_id');
    }
}
