<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Collector;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\OptimizerFilterInterface;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Collection\ProviderInterface;

/**
 * Applied optimizers collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Optimizers implements CollectorInterface
{
    /**
     * Collector type
     */
    const TYPE = 'optimizers';

    /**
     * @var \Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Collection\ProviderInterface
     */
    private $provider;

    /**
     * @var Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\OptimizerFilterInterface[]
     */
    private $filters;

    /**
     * Optimizers constructor.
     *
     * @param ProviderInterface          $provider Optimizers Provider
     * @param OptimizerFilterInterface[] $filters  Optimizer filters.
     */
    public function __construct(ProviderInterface $provider, array $filters = [])
    {
        $this->provider = $provider;
        $this->filters  = $filters;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        return [self::TYPE => $this->getOptimizers($containerConfiguration)];
    }

    /**
     * @return array
     */
    private function getOptimizers(ContainerConfigurationInterface $containerConfiguration)
    {
        $optimizers = $this->provider->getCollection($containerConfiguration);
        $results    = [];

        /** @var OptimizerInterface $optimizer */
        foreach ($optimizers as $optimizer) {
            $results[$optimizer->getId()] = [
                'name'  => $optimizer->getName(),
                'boost' => $optimizer->getConfig('constant_score_value'),
                'rule'  => $optimizer->getRuleCondition()->getConditions()->asStringRecursive(),
            ];
        }

        if (isset($this->filters[$containerConfiguration->getName()])) {
            $optimizerIds = $this->filters[$containerConfiguration->getName()]->getOptimizerIds() ?? array_keys($results);
            $results      = array_intersect_key($results, array_flip($optimizerIds));
        }

        return $results;
    }
}
