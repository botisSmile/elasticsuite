<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Controller\Adminhtml\Explain;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Search\Model\QueryFactory;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterfaceFactory;
use Smile\ElasticsuiteExplain\Model\ResultFactory as ResultModelFactory;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCore\Search\Request\ContainerConfigurationFactory;

/**
 * Explain Adminhtml Index controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Results extends Action
{
    /**
     * @var \Smile\ElasticsuiteExplain\Model\ResultFactory
     */
    private $resultModelFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var OptimizerInterfaceFactory
     */
    private $optimizerFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\ContainerConfigurationFactory
     */
    private $containerConfigFactory;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Search\ContextInterface
     */
    private $searchContext;

    /**
     * Constructor.
     *
     * @param Context                       $context                Controller  context.
     * @param ResultModelFactory            $resultModelFactory     Result model factory.
     * @param CategoryRepositoryInterface   $categoryRepository     Category Repository
     * @param OptimizerInterfaceFactory     $optimizerFactory       OptimzerFactory
     * @param ContainerConfigurationFactory $containerConfigFactory Container Configuration Factory
     * @param JsonHelper                    $jsonHelper             JSON Helper.
     * @param QueryFactory                  $queryFactory           Query Factory.
     * @param ContextInterface              $searchContext          Search context.
     */
    public function __construct(
        Context $context,
        ResultModelFactory $resultModelFactory,
        CategoryRepositoryInterface $categoryRepository,
        OptimizerInterfaceFactory $optimizerFactory,
        ContainerConfigurationFactory $containerConfigFactory,
        JsonHelper $jsonHelper,
        QueryFactory $queryFactory,
        ContextInterface $searchContext
    ) {
        parent::__construct($context);

        $this->optimizerFactory       = $optimizerFactory;
        $this->categoryRepository     = $categoryRepository;
        $this->resultModelFactory     = $resultModelFactory;
        $this->containerConfigFactory = $containerConfigFactory;
        $this->jsonHelper             = $jsonHelper;
        $this->queryFactory           = $queryFactory;
        $this->searchContext          = $searchContext;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $responseData = $this->getResultObject()->getData();
        $json         = $this->jsonHelper->jsonEncode($responseData);

        $this->getResponse()->representJson($json);
    }

    /**
     * Check if allowed to manage optimizer.
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Smile_ElasticsuiteCatalogOptimizer::manage');
    }

    /**
     * Load and initialize the result model.
     *
     * @return \Smile\ElasticsuiteExplain\Model\Result
     */
    private function getResultObject()
    {
        $pageSize        = $this->getPageSize();
        $queryText       = $this->getQueryText();
        $category        = $this->getCategory();
        $containerConfig = $this->getContainerConfiguration();

        $this->updateSearchContext($this->getStoreId(), $category, $queryText);

        return $this->resultModelFactory->create(
            [
                'containerConfig' => $containerConfig,
                'category'        => $category,
                'queryText'       => $queryText,
                'size'            => $pageSize,
            ]
        );
    }

    /**
     * Load current category using the request params.
     *
     * @return CategoryInterface
     */
    private function getCategory()
    {
        $storeId    = $this->getStoreId();
        $categoryId = $this->getCategoryId();
        $category   = null;

        if ($this->getCategoryId()) {
            $category = $this->categoryRepository->get($categoryId, $storeId);
        }

        return $category;
    }

    /**
     * Return the preview page size.
     *
     * @return int
     */
    private function getPageSize()
    {
        return (int) $this->getRequest()->getParam('page_size');
    }

    /**
     * Return the container to preview.
     *
     * @return ContainerConfigurationInterface
     */
    private function getContainerConfiguration()
    {
        $containerName = $this->getRequest()->getParam('search_container_preview');

        $containerConfig = $this->containerConfigFactory->create(
            ['containerName' => $containerName, 'storeId' => $this->getStoreId()]
        );

        return $containerConfig;
    }

    /**
     * Return the query text to preview.
     *
     * @return string
     */
    private function getQueryText()
    {
        $queryText = trim(strtolower((string) $this->getRequest()->getParam('query_text_preview', '')));

        if ($queryText == '') {
            $queryText = null;
        }

        return $queryText;
    }

    /**
     * Return the category to preview.
     *
     * @return int
     */
    private function getCategoryId()
    {
        return $this->getRequest()->getParam('category_preview');
    }

    /**
     * Return the store id to preview.
     *
     * @return int
     */
    private function getStoreId()
    {
        return $this->getRequest()->getParam('store_id');
    }

    /**
     * Update the search context using current store id, category or query text.
     *
     * @param integer           $storeId   Store id.
     * @param CategoryInterface $category  Category.
     * @param string            $queryText Fulltext query text.
     *
     * @return void
     */
    private function updateSearchContext($storeId, $category, $queryText)
    {
        $this->searchContext->setStoreId($storeId);

        if ((string) $queryText !== '') {
            try {
                $query = $this->queryFactory->create()->loadByQueryText($queryText);
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                $query = $this->queryFactory->create();
            }

            if ((string) $query->getQueryText() === '') {
                $query->setQueryText($queryText);
            }

            $this->searchContext->setCurrentSearchQuery($query);
        } elseif ($category && $category->getId()) {
            $this->searchContext->setCurrentCategory($category);
        }
    }
}
