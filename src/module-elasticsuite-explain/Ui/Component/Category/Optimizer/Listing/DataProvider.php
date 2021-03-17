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

namespace Smile\ElasticsuiteExplain\Ui\Component\Category\Optimizer\Listing;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Category\OptimizerFilter;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory;
use Smile\ElasticsuiteCatalogOptimizer\Ui\Component\Optimizer\Listing\DataProvider as BaseDataProvider;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteExplain\Model\Renderer\Optimizer as OptimizerRenderer;

/**
 * Optimizer listing data provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class DataProvider extends BaseDataProvider
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
     * @var OptimizerRenderer
     */
    private $optimizerRenderer;

    /**
     * DataProvider Constructor.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param string                           $name                Component name
     * @param string                           $primaryFieldName    Primary field Name
     * @param string                           $requestFieldName    Request field name
     * @param CollectionFactory                $collectionFactory   The collection factory
     * @param RequestInterface                 $request             Request
     * @param CategoryRepositoryInterface      $categoryRepository  Category repository
     * @param ContextInterface                 $searchContext       Search context
     * @param OptimizerFilter                  $optimizerFilter     Optimizer filter
     * @param OptimizerRenderer                $optimizerRenderer   Optimizer Renderer
     * @param AddFieldToCollectionInterface[]  $addFieldStrategies  Add field Strategy
     * @param AddFilterToCollectionInterface[] $addFilterStrategies Add filter Strategy
     * @param array                            $meta                Component Meta
     * @param array                            $data                Component extra data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        ContextInterface $searchContext,
        OptimizerFilter $optimizerFilter,
        OptimizerRenderer $optimizerRenderer,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $this->request            = $request;
        $this->optimizerFilter    = $optimizerFilter;
        $this->categoryRepository = $categoryRepository;
        $this->searchContext      = $searchContext;
        $this->optimizerRenderer  = $optimizerRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $categoryId = (int) $this->request->getParam('category_id');
        if (!$categoryId) {
            return parent::getData();
        }

        // Display in the list only optimizers potentially applied for a category.
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
                ->addFieldToSelect(['config', 'rule_condition'])
                ->addFieldToFilter('optimizer_id', ['in' => $this->getOptimizerIds($categoryId)]);
        }

        $itemsData = [];
        /** @var Optimizer $optimizer */
        foreach ($this->getCollection()->getItems() as $optimizer) {
            $itemsData[] = array_merge(
                $optimizer->toArray(),
                [
                    'boost' => $this->optimizerRenderer->renderBoost($optimizer),
                    'rule' => $this->optimizerRenderer->renderRuleConditions($optimizer),
                ]
            );
        }

        return ['totalRecords' => count($itemsData), 'items' => $itemsData];
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
