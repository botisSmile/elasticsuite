<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteFacetRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteFacetRecommender\ViewModel\Navigation;

/**
 * Facet Recommender View Model
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class FilterRecommender implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const BASE_RECOMMENDER_URL = 'rest/V1/elasticsuite-facet-recommender/';

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    private $dataProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Generic tracker helper
     *
     * @var \Smile\ElasticsuiteTracker\Helper\Data
     */
    private $trackerHelper;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * FilterRecommender constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager        Store Manager
     * @param \Magento\Catalog\Model\Layer\Resolver                            $layerResolver       Layer Resolver
     * @param \Smile\ElasticsuiteTracker\Helper\Data                           $trackerHelper       Tracker Helper
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $dataProviderFactory Data Provider
     * @param \Magento\Framework\UrlInterface                                  $url                 URL
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Smile\ElasticsuiteTracker\Helper\Data $trackerHelper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $dataProviderFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->dataProvider  = $dataProviderFactory->create(['layer' => $layerResolver->get()]);
        $this->storeManager  = $storeManager;
        $this->trackerHelper = $trackerHelper;
        $this->url           = $url;
    }

    /**
     * Get category Id.
     *
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->dataProvider->getCategory()->getId();
    }

    /**
     * Get Current category name.
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->dataProvider->getCategory()->getName();
    }

    /**
     * Get tracking cookie configuration.
     *
     * @return array
     */
    public function getCookieConfig()
    {
        return $this->trackerHelper->getCookieConfig();
    }

    /**
     * Get recommender API base URL.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->url->getUrl(self::BASE_RECOMMENDER_URL);
    }
}
