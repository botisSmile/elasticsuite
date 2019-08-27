<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Controller\Adminhtml\Gauge;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Smile\ElasticsuiteMerchandisingGauge\Model\Category\MeasureFactory;
use Smile\ElasticsuiteMerchandisingGauge\Model\MeasureInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Class Measure
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class Measure extends Action
{
    /**
     * @var MeasureFactory
     */
    private $measureFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * Measure constructor.
     *
     * @param Context $context    Controller context
     * @param Data    $jsonHelper Json Helper.
     *
      @param \Magento\Catalog\Model\CategoryFactory                  $categoryFactory     Category factory.
     */
    public function __construct(
        Context $context,
        MeasureFactory $measureFactory,
        CategoryFactory $categoryFactory,
        Data $jsonHelper
    ) {
        parent::__construct($context);

        $this->measureFactory = $measureFactory;
        $this->categoryFactory = $categoryFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $products      = $this->getRequest()->getParam('products', []);
        $productPositions = $this->getRequest()->getParam('product_position', []);
        $blacklistedProducts = $this->getRequest()->getParam('blacklisted_products', []);

        $responseData = $this->getMeasureObject()->getData();
        $json         = $this->jsonHelper->jsonEncode($responseData);

        $this->getResponse()->representJson($json);
    }

    /**
     * Returns the measure object
     *
     * @return MeasureInterface
     */
    private function getMeasureObject()
    {
        $category = $this->getCategory();
        $pageSize = $this->getPageSize();

        $measureObject = $this->measureFactory->create(['category' => $category, 'size' => $pageSize]);

        return $measureObject;
    }

    /**
     * Return the category to take the measure of.
     * Applies current admin modifications to the category (added and removed products, updated virtual rule, etc).
     *
     * @return CategoryInterface
     */
    private function getCategory()
    {
        $category = $this->loadCategory();

        $this->addVirtualCategoryData($category)
            ->addSelectedProducts($category)
            ->setSortedProducts($category)
            ->setBlacklistedProducts($category);

        return $category;
    }

    /**
     * Load current category using the request params.
     *
     * @return CategoryInterface
     */
    private function loadCategory()
    {
        $category   = $this->categoryFactory->create();
        $storeId    = $this->getRequest()->getParam('store');
        $categoryId = $this->getRequest()->getParam('entity_id');

        $category->setStoreId($storeId)->load($categoryId);

        return $category;
    }

    /**
     * Append virtual rule params to the category.
     *
     * @param CategoryInterface $category Category.
     *
     * @return $this
     */
    private function addVirtualCategoryData(CategoryInterface $category)
    {
        $isVirtualCategory = (bool) $this->getRequest()->getParam('is_virtual_category');
        $category->setIsVirtualCategory($isVirtualCategory);

        if ($isVirtualCategory) {
            $category->getVirtualRule()->loadPost($this->getRequest()->getParam('virtual_rule', []));
            $category->setVirtualCategoryRoot($this->getRequest()->getParam('virtual_category_root', null));
        }

        return $this;
    }

    /**
     * Add user selected products.
     *
     * @param CategoryInterface $category Category.
     *
     * @return $this
     */
    private function addSelectedProducts(CategoryInterface $category)
    {
        $selectedProducts = $this->getRequest()->getParam('selected_products', []);

        $addedProducts = isset($selectedProducts['added_products']) ? $selectedProducts['added_products'] : [];
        $category->setAddedProductIds($addedProducts);

        $deletedProducts = isset($selectedProducts['deleted_products']) ? $selectedProducts['deleted_products'] : [];
        $category->setDeletedProductIds($deletedProducts);

        return $this;
    }

    /**
     * Append products sorted by the user to the category.
     *
     * @param CategoryInterface $category Category.
     *
     * @return $this
     */
    private function setSortedProducts(CategoryInterface $category)
    {
        $productPositions = $this->getRequest()->getParam('product_position', []);
        // $category->setSortedProductIds(array_keys($productPositions));
        asort($productPositions);
        $productPositions = array_flip($productPositions);
        $category->setSortedProductIds($productPositions);

        return $this;
    }

    /**
     * Appends products blacklisted by the user in the category.
     *
     * @param CategoryInterface $category Category.
     *
     * @return $this
     */
    private function setBlacklistedProducts(CategoryInterface $category)
    {
        $blacklistedProducts = $this->getRequest()->getParam('blacklisted_products', []);
        $category->setBlacklistedProductIds(array_map('intval', $blacklistedProducts));

        return $this;
    }

    /**
     * Return the measure page size (which is the same as the preview).
     *
     * @return int
     */
    private function getPageSize()
    {
        return (int) $this->getRequest()->getParam('page_size');
    }
}
