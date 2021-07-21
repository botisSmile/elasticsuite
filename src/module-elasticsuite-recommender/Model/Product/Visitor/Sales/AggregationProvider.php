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

namespace Smile\ElasticsuiteRecommender\Model\Product\Visitor\Sales;

use Smile\ElasticsuiteRecommender\Model\Product\Visitor\AggregationProviderInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory;

/**
 * Bought products aggregation provider
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 */
class AggregationProvider implements AggregationProviderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var AggregationFactory
     */
    private $aggregationFactory;

    /**
     * @var string
     */
    private $name;

    /**
     * AggregationProvider constructor.
     *
     * @param QueryFactory       $queryFactory       Query factory.
     * @param AggregationFactory $aggregationFactory Aggregation factory.
     * @param string             $name               Aggregation name.
     */
    public function __construct(QueryFactory $queryFactory, AggregationFactory $aggregationFactory, $name = 'product_sale')
    {
        $this->queryFactory = $queryFactory;
        $this->aggregationFactory = $aggregationFactory;
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregationName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregation($size, $categories = [])
    {
        $categoryFilter = null;
        if (!empty($categories)) {
            $filterQuery = $this->queryFactory->create(
                QueryInterface::TYPE_TERMS,
                [
                    'field'  => 'page.order.items.category_ids',
                    'values' => $categories,
                ]
            );
            $categoryFilter = $this->queryFactory->create(QueryInterface::TYPE_BOOL, ['must' => [$filterQuery]]);
        }

        $aggParams = [
            'name'          => $this->getAggregationName(),
            'field'         => 'page.order.items.product_id',
            'nestedPath'    => 'page.order.items',
            'nestedFilter'  => $categoryFilter,
            'size'          => $size,
            'minDocCount'   => 1,
        ];

        return $this->aggregationFactory->create(BucketInterface::TYPE_SIGNIFICANT_TERM, $aggParams);
    }
}
