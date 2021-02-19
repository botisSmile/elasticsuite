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

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

/**
 * Provides access to products in cart
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class CartProductProvider
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var array
     */
    private $cartProductIds;

    /**
     * Constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession Checkout session.
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve ids of products in cart
     *
     * @return array
     */
    public function getCartProductIds()
    {
        if ($this->cartProductIds === null) {
            $productIds = [];
            foreach ($this->checkoutSession->getQuote()->getAllItems() as $quoteItem) {
                /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
                $productIds[] = $quoteItem->getProductId();
            }
            $this->cartProductIds = array_filter($productIds);
        }

        return $this->cartProductIds;
    }
}
