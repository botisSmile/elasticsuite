<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Helper\Product;

use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Framework\App\ActionInterface;

/**
 * Custom Wishlist Helper
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Wishlist extends WishlistHelper
{
    /**
     * Retrieve params for adding product to wishlist.
     * Uses the ajax loading block provided 'uenc' parameter to avoid using the current (ie the ajax controller's) URL
     * as redirect URL.
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item   Item.
     * @param array                                                       $params Params.
     *
     * @return string
     */
    public function getAddParams($item, array $params = [])
    {
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $productId = $item->getProductId();
        }

        $url = $this->_getUrlStore($item)->getUrl('wishlist/index/add');
        if ($productId) {
            $params['product'] = $productId;
        }

        if ($encodedUrl = $this->_getRequest()->getParam(ActionInterface::PARAM_NAME_URL_ENCODED)) {
            $params[ActionInterface::PARAM_NAME_URL_ENCODED] = $encodedUrl;
        }

        return $this->_postDataHelper->getPostData($url, $params);
    }
}
