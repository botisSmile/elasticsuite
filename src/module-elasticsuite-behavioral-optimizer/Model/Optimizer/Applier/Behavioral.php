<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralOptimizer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteBehavioralOptimizer\Model\Optimizer\Applier;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\ApplierInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Applier model for optimizers based on attributes values.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Behavioral implements ApplierInterface
{
    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    private $queryFactory;

    /**
     * Behavioral constructor.
     *
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory Query Factory
     */
    public function __construct(
        QueryFactory $queryFactory
    ) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunction(ContainerConfigurationInterface $containerConfiguration, OptimizerInterface $optimizer)
    {
        try {
            $field = $containerConfiguration->getMapping()->getField($optimizer->getConfig('metric'));
            $field = $field->getName();
        } catch (\LogicException $exception) {
            return null;
        }

        $scaleFactor = (float) $optimizer->getConfig('scale_factor');

        $function = [
            'field_value_factor' => [
                'field'    => $field,
                'factor'   => $scaleFactor,
                'modifier' => $optimizer->getConfig('scale_function'),
                'missing'  => 1 / $scaleFactor,
            ],
            'filter' => $this->getFilter($optimizer, $field),
        ];

        return $function;
    }

    /**
     * Compute the optimizer filter. Add a clause on the chosen metric field to properly ignore missing.
     *
     * @param OptimizerInterface $optimizer The optimizer
     * @param string             $field     The field
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    public function getFilter($optimizer, $field)
    {
        $baseFilter = $optimizer->getRuleCondition()->getSearchQuery();

        $filter = $this->queryFactory->create(
            QueryInterface::TYPE_BOOL,
            [
                'must' => [
                    $baseFilter,
                    $this->queryFactory->create(QueryInterface::TYPE_EXISTS, ['field' => $field]),
                ],
            ]
        );

        return $filter;
    }
}
