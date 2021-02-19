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

namespace Smile\ElasticsuiteFacetRecommender\Api\Data;

/**
 * Facet recommendation interface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface FacetRecommendationInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getValue();
}
