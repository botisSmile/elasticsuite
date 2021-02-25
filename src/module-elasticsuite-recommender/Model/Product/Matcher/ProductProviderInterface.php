<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Product provider interface for the multiple product matcher
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
interface ProductProviderInterface
{
    /**
     * Return the products to get recommendations for
     *
     * @return ProductInterface[]
     */
    public function getProducts();
}
