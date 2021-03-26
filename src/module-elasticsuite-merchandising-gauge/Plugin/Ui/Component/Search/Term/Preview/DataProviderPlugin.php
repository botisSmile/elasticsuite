<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Plugin\Ui\Component\Search\Term\Preview;

use Smile\ElasticsuiteCatalog\Ui\Component\Search\Term\Preview\DataProvider;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

/**
 * Class DataProviderPlugin
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
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
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor.
     *
     * @param UrlInterface          $urlBuilder   Admin URL Builder.
     * @param StoreManagerInterface $storeManager Store Manager.
     * @param QueryFactory          $queryFactory Query factory.
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory
    ) {
        $this->urlBuilder   = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->queryFactory = $queryFactory;
    }

    /**
     * Append gauge ajax url.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param DataProvider $dataProvider Data provider.
     * @param \Closure     $proceed      Original method.
     *
     * @return array
     */
    public function aroundGetData(DataProvider $dataProvider, \Closure $proceed)
    {
        $data = $proceed();

        foreach ($data as &$queryData) {
            $queryData['product_sorter_gauge_load_url'] = $this->getProductSorterGaugeLoadUrl((int) $queryData['store_id']);
        }

        return $data;
    }

    /**
     * Retrieve the category product sorter load URL.
     *
     * @param int $storeId Store ID.
     *
     * @return string|null
     */
    private function getProductSorterGaugeLoadUrl($storeId)
    {
        $url = null;

        if ($storeId === 0) {
            $storeId = $this->getDefaultStoreId();
        }

        if ($storeId) {
            $urlParams = ['ajax' => true, 'store' => $storeId];
            $url = $this->urlBuilder->getUrl('merchgauge/term_merchandiser/measure', $urlParams);
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
}
