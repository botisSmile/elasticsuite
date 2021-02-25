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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Helper\Product;

use Magento\Catalog\Helper\Product\Compare as ProductCompareHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;

/**
 * Custom Compare Product Helper
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Compare extends ProductCompareHelper
{
    /**
     * Get parameters used for build add product to compare list urls.
     * Uses the ajax loading block provided 'uenc' parameter to avoid using the current (ie the ajax controller's) URL
     * as redirect URL.
     *
     * @param Product $product Product to get parameters for
     *
     * @return string
     */
    public function getPostDataParams($product)
    {
        $data = ['product' => $product->getId()];
        if ($encodedUrl = $this->_getRequest()->getParam(ActionInterface::PARAM_NAME_URL_ENCODED)) {
            $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $encodedUrl;
        }

        return $this->postHelper->getPostData($this->getAddUrl(), $data);
    }
}
