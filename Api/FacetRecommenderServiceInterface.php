<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteFacetRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteFacetRecommender\Api;

/**
 * Smart facets service interface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface FacetRecommenderServiceInterface
{
    /**
     * Retrieve facets recommendations for the current category and user.
     *
     * @param string $visitorId  The visitor Id
     * @param string $userId     The user Id
     * @param int    $categoryId The category Id
     *
     * @return \Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterface[]
     */
    public function getFacetsRecommendations($visitorId, $userId, $categoryId);
}
