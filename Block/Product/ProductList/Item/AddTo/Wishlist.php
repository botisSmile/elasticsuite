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

namespace Smile\ElasticsuiteRecommender\Block\Product\ProductList\Item\AddTo;

use Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist as AddToWishlist;
use Smile\ElasticsuiteRecommender\Helper\Product\Wishlist as WishlistHelper;

/**
 * Add to wishlist custom block
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Wishlist extends AddToWishlist
{
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context        Product context.
     * @param WishlistHelper                         $wishlistHelper Wishlist helper.
     * @param array                                  $data           Data.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        WishlistHelper $wishlistHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_wishlistHelper = $wishlistHelper;
    }
}
