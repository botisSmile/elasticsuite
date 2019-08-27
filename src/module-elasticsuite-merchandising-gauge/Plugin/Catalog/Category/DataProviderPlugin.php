<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Plugin\Catalog\Category;

use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Category;

/**
 * Category form UI data provider extension.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class DataProviderPlugin
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param UrlInterface          $urlBuilder   Admin URL Builder.
     * @param StoreManagerInterface $storeManager Store Manager.
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder   = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Append virtual rule and sorting product data.
     *
     * @param CategoryDataProvider $dataProvider Data provider.
     * @param \Closure             $proceed      Original method.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetData(CategoryDataProvider $dataProvider, \Closure $proceed)
    {
        $data = $proceed();

        $currentCategory = $dataProvider->getCurrentCategory();

        $data[$currentCategory->getId()]['product_sorter_gauge_load_url'] = $this->getProductSorterGaugeLoadUrl($currentCategory);

        return $data;
    }

    /**
     * Retrieve the category product sorter load URL.
     *
     * @param Category $category Category.
     *
     * @return string|null
     */
    private function getProductSorterGaugeLoadUrl(Category $category)
    {
        $url = null;

        $storeId = $this->getStoreId($category);

        if ($storeId) {
            $urlParams = ['ajax' => true, 'store' => $storeId];
            $url = $this->urlBuilder->getUrl('merchgauge/gauge/measure', $urlParams);
        }

        return $url;
    }

    /**
     * Retrieve default store view id.
     *
     * @return int
     */
    private function getDefaultStoreId()
    {
        $store = $this->storeManager->getDefaultStoreView();

        if (null === $store) {
            // Occurs when current user does not have access to default website (due to AdminGWS ACLS on Magento EE).
            $store = !empty($this->storeManager->getWebsites()) ? current($this->storeManager->getWebsites())->getDefaultStore() : null;
        }

        return $store ? $store->getId() : 0;
    }

    /**
     * Get store id for the current category.
     *
     *
     * @param Category $category Category.
     *
     * @return int
     */
    private function getStoreId(Category $category)
    {
        $storeId = $category->getStoreId();

        if ($storeId === 0) {
            $defaultStoreId   = $this->getDefaultStoreId();
            $categoryStoreIds = array_filter($category->getStoreIds());
            $storeId        = current($categoryStoreIds);
            if (in_array($defaultStoreId, $categoryStoreIds)) {
                $storeId = $defaultStoreId;
            }
        }

        return $storeId;
    }
}
