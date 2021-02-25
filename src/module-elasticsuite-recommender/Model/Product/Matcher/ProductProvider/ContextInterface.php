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

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider;

/**
 * Product provider context interface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
interface ContextInterface
{
    /**
     * Return category ids to contextualize the products to be provided
     *
     * @return int[]
     */
    public function getCategories();

    /**
     * Return the max age in days to go looking for products
     *
     * @return int
     */
    public function getMaxAge();

    /**
     * Return the maximum number of products to return
     *
     * @return int
     */
    public function getMaxSize();
}
