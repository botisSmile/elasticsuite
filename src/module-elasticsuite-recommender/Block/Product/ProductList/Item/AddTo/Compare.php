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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Block\Product\ProductList\Item\AddTo;

use Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare as AddToCompare;
use Smile\ElasticsuiteRecommender\Helper\Product\Compare as ProductCompareHelper;

/**
 * Add to compare custom block
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Compare extends AddToCompare
{
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context       Product context.
     * @param ProductCompareHelper                   $compareHelper Compare helper.
     * @param array                                  $data          Data.
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        ProductCompareHelper $compareHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_compareProduct = $compareHelper;
    }
}
