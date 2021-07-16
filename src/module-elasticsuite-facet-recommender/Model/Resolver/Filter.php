<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteFacetRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteFacetRecommender\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * ElasticSuite Smart facet resolver
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Filter implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        return isset($data['attribute_code'])
        && isset($data['label'])
        && isset($data['value'])
        && count($data) == 3 ? 'ElasticsuiteFilterRecommendation' : '';
    }
}
