<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Visitor;

use Smile\ElasticsuiteCore\Search\Request\BucketInterface;

/**
 * Visitor product provider aggregation provider interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
interface AggregationProviderInterface
{
    /**
     * Return the aggregation name
     *
     * @return mixed
     */
    public function getAggregationName();

    /**
     * Return the aggregation
     *
     * @param integer $size       Aggregation size.
     * @param array   $categories Contextual categories.
     *
     * @return BucketInterface
     */
    public function getAggregation($size, $categories = []);
}
