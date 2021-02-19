<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralAutocomplete
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
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
