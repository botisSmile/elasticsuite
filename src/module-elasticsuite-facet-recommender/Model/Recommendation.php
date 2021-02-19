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

namespace Smile\ElasticsuiteFacetRecommender\Model;

use Magento\Framework\DataObject;
use Smile\ElasticsuiteFacetRecommender\Api\Data\FacetRecommendationInterface;

/**
 * ElasticSuite smart facet Recommendation implementation
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Recommendation extends DataObject implements FacetRecommendationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData('value');
    }
}
