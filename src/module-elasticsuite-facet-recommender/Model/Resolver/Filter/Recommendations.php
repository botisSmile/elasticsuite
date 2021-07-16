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

namespace Smile\ElasticsuiteFacetRecommender\Model\Resolver\Filter;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Smile\ElasticsuiteFacetRecommender\Model\Recommender\Service;

/**
 * ElasticSuite Smart facets graphql implementation
 *
 * @category Smile
 * @package  Smile\ElasticsuiteFacetRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Recommendations implements ResolverInterface
{
    /**
     * Recommendations constructor.
     *
     * @param \Smile\ElasticsuiteFacetRecommender\Model\Recommender\Service $service Recommendations service.
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateArgs($args);

        $filters = $this->service->getFacetsRecommendations($args['visitorId'], $args['userId'], $args['categoryId']);
        $items   = [];

        foreach ($filters as $filter) {
            $items[] = [
                'attribute_code' => $filter['name'],
                'label'          => $filter['name'],
                'value'          => $filter['value'],
            ];
        }

        return [
            'items' => $items,
        ];
    }

    /**
     * Validate GraphQL query arguments and throw exception if needed.
     *
     * @param array $args GraphQL query arguments
     *
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args)
    {
        if (!isset($args['categoryId']) || ((int) $args['categoryId'] === 0)) {
            throw new GraphQlInputException(
                __("'categoryId' input argument is required.")
            );
        }
        if (!isset($args['visitorId']) || ((string) $args['visitorId'] === '')) {
            throw new GraphQlInputException(
                __("'visitorId' input argument is required.")
            );
        }
        if (!isset($args['userId']) || ((string) $args['userId'] === '')) {
            throw new GraphQlInputException(
                __("'userId' input argument is required.")
            );
        }
    }
}
