<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign\Limitation;

use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\PopularSearchTerms;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Store\Model\ScopeInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteCatalog\Block\CatalogSearch\Result\Cache;

/**
 * Campaign Limitation identities.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Identities
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Collection
     */
    private $queryCollection;

    /**
     * @var CampaignInterface
     */
    private $campaign;

    /**
     * Limitation Identities Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig     Scope Config
     * @param Collection           $queryCollection Search Queries Collection
     * @param CampaignInterface    $campaign        The campaign
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Collection $queryCollection,
        CampaignInterface $campaign
    ) {
        $this->scopeConfig     = $scopeConfig;
        $this->queryCollection = $queryCollection;
        $this->campaign        = $campaign;
    }

    /**
     * Get Limitation identities for the current campaign.
     *
     * @return array
     */
    public function get()
    {
        $identities = [];
        $origData   = $this->campaign->getOrigData();
        $containers = $this->campaign->getData('search_containers') ?? [];

        if (!$this->campaign->isObjectNew()) {
            $containers = array_unique(
                array_keys(array_merge($this->campaign->getSearchContainers(), $origData['search_containers'] ?? []))
            );
        }

        if (in_array('quick_search_container', $containers)) {
            $identities = array_merge($identities, $this->getSearchQueryIdentities());
        }

        if (in_array('catalog_view_container', $containers)) {
            $identities = array_merge($identities, $this->getCategoryIdentities());
        }

        return $identities;
    }

    /**
     * Get search queries identities related to current optimizer.
     *
     * @return array
     */
    private function getSearchQueryIdentities()
    {
        $identities = [];
        $queryIds   = [];
        $origData   = $this->campaign->getOrigData();
        $data       = $this->campaign->getData();

        // If optimizer was previously assigned to all queries, or is now set to all queries.
        $isAppliedToAllQueries = empty($data['quick_search_container'])
            || (bool) $data['quick_search_container']['apply_to'] === false;
        $wasAppliedToAllQueries = empty($origData['quick_search_container']['query_ids']);

        if (!empty($origData['quick_search_container']['query_ids'])) {
            $queryIds = array_merge($queryIds, $origData['quick_search_container']['query_ids']);
        }

        if (!empty($data['quick_search_container']['query_ids'])) {
            foreach ($data['quick_search_container']['query_ids'] as $query) {
                $queryIds[] = $query['id'] ?? $query;
            }
        }

        $queryIds = array_unique(array_filter($queryIds));

        if ($wasAppliedToAllQueries || $isAppliedToAllQueries) {
            $identities[] = Cache::POPULAR_SEARCH_CACHE_TAG;
        } elseif (!empty($queryIds)) {
            $popularQueryIds = $this->queryCollection
                ->setPopularQueryFilter($this->campaign->getStoreId())
                ->setPageSize($this->getMaxCountCacheableSearchTerms($this->campaign->getStoreId()))
                ->load()
                ->getColumnValues('query_id');

            if (!empty(array_intersect($queryIds, $popularQueryIds))) {
                $identities[] = Cache::POPULAR_SEARCH_CACHE_TAG;
            }
        }

        return $identities;
    }

    /**
     * Get category identities related to current optimizer.
     *
     * @return array
     */
    private function getCategoryIdentities()
    {
        $identities  = [];
        $categoryIds = [];
        $origData    = $this->campaign->getOrigData();
        $data        = $this->campaign->getData();

        // If optimizer was previously assigned to all categories, or is now set to all categories.
        $isAppliedToAllCategories = empty($data['catalog_view_container'])
            || (bool) $data['catalog_view_container']['apply_to'] === false;

        $wasAppliedToAllCategories = empty($origData['catalog_view_container']['category_ids']);

        if ($isAppliedToAllCategories || $wasAppliedToAllCategories) {
            $identities[] = Category::CACHE_TAG;
        }

        if (!empty($data['catalog_view_container']['category_ids'])) {
            $categoryIds = array_merge($categoryIds, $data['catalog_view_container']['category_ids']);
        }

        if (!empty($origData['catalog_view_container']['category_ids'])) {
            $categoryIds = array_merge($categoryIds, $origData['catalog_view_container']['category_ids']);
        }

        $categoryIds = array_filter(array_unique($categoryIds));
        if (!empty($categoryIds)) {
            $categoryTags = array_map(function ($categoryId) {
                return Category::CACHE_TAG . '_' . $categoryId;
            }, $categoryIds);

            $identities = array_merge($identities, $categoryTags);
        }

        return $identities;
    }

    /**
     * Retrieve maximum count cacheable search terms by Store.
     *
     * @param int $storeId Store Id
     * @return int
     */
    private function getMaxCountCacheableSearchTerms(int $storeId)
    {
        return $this->scopeConfig->getValue(
            PopularSearchTerms::XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
