<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralAutocomplete
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBehavioralAutocomplete\Api;

/**
 * Trending Queries service interface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralAutocomplete
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface TrendingQueryServiceInterface
{
    /**
     * @param string|null $queryText The query text.
     * @param int|null    $maxSize   Max size of results.
     *
     * @return \Magento\Search\Model\QueryInterface[]
     */
    public function get($queryText = null, $maxSize = null);
}
