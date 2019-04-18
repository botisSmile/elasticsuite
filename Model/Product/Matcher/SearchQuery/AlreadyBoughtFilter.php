<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQuery;

use Magento\Store\Ui\Component\Listing\Column\Store;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\SearchQueryBuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\CoOccurrence;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Smile\ElasticsuiteRecommender\Helper\Data;
use Smile\ElasticsuiteTracker\Helper\Data as TrackerHelper;

/**
 * Generic search query products already bought exclusion filter builder
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class AlreadyBoughtFilter implements SearchQueryBuilderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var CoOccurrence
     */
    private $coOccurrence;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var TrackerHelper
     */
    private $trackerHelper;

    /**
     * @var int
     */
    const MAX_ITEMS = 20;

    /**
     * Constructor.
     *
     * @param QueryFactory           $queryFactory  Query factory.
     * @param CoOccurrence           $coOccurrence  Co-occurrence finder.
     * @param Data                   $helper        Data helper.
     * @param StoreManagerInterface  $storeManager  Store manager.
     * @param CookieManagerInterface $cookieManager Cookie Manager
     * @param TrackerHelper          $trackerHelper Tracker Helper.
     */
    public function __construct(
        QueryFactory $queryFactory,
        CoOccurrence $coOccurrence,
        Data $helper,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        TrackerHelper $trackerHelper
    ) {
        $this->queryFactory     = $queryFactory;
        $this->coOccurrence     = $coOccurrence;
        $this->storeManager     = $storeManager;
        $this->helper           = $helper;
        $this->cookieManager    = $cookieManager;
        $this->trackerHelper    = $trackerHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchQuery(ProductInterface $product)
    {
        $query = false;

        if ($this->helper->isExcludingPastBoughtProducts()) {
            $excludedProducts = $this->getPastBoughtProducts();
            if (!empty($excludedProducts)) {
                $query = $this->queryFactory->create(
                    QueryInterface::TYPE_TERMS,
                    ['field' => 'entity_id', 'values' => $excludedProducts]
                );
            }
        }

        return $query;
    }

    /**
     * Return the list of product ids the user bought
     *
     * @return array
     */
    private function getPastBoughtProducts()
    {
        $boughtProducts = [];

        if ($visitorId = $this->getCurrentVisitor()) {
            $storeId = $this->storeManager->getStore()->getId();
            $boughtProducts = $this->coOccurrence->getCoOccurrences(
                'visitor_id',
                $visitorId,
                $storeId,
                'product_sale',
                self::MAX_ITEMS
            );
        }

        return $boughtProducts;
    }

    /**
     * Return the current visitor id.
     *
     * @return string
     */
    private function getCurrentVisitor()
    {
        $visitorId = null;

        $cookieConfig = $this->trackerHelper->getCookieConfig();
        if (array_key_exists('visitor_cookie_name', $cookieConfig)) {
            $visitorCookieName = $cookieConfig['visitor_cookie_name'];
            $visitorId = $this->readCookieValue($visitorCookieName);
        }

        return $visitorId;
    }

    /**
     * Read cookie value.
     *
     * @param string $cookieName Cookie name.
     *
     * @return string|NULL
     */
    private function readCookieValue($cookieName)
    {
        return $this->cookieManager->getCookie($cookieName);
    }
}
