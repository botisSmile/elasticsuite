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

use Magento\Framework\UrlInterface;
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
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var OptimizerFilterInterface[]
     */
    private $filters;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Optimizers constructor.
     *
     * @param ProviderInterface          $provider   Optimizers Provider
     * @param UrlInterface               $urlBuilder URL builder.
     * @param OptimizerFilterInterface[] $filters    Optimizer filters.
     */
    public function __construct(ProviderInterface $provider, UrlInterface $urlBuilder, $filters = [])
    {
        $this->provider   = $provider;
        $this->urlBuilder = $urlBuilder;
        $this->filters    = $filters;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        return [self::TYPE => $this->getOptimizers($containerConfiguration)];
    }

    /**
     * Get applicable optimizers.
     *
     * @param ContainerConfigurationInterface $containerConfiguration Container configuration.
     *
     * @return array
     */
    private function getOptimizers(ContainerConfigurationInterface $containerConfiguration)
    {
        $optimizers = $this->provider->getCollection($containerConfiguration);
        $results    = [];

        /** @var OptimizerInterface $optimizer */
        foreach ($optimizers as $optimizer) {
            $results[$optimizer->getId()] = [
                'id'        => $optimizer->getId(),
                'name'      => $optimizer->getName(),
                'boost'     => $optimizer->getConfig('constant_score_value'),
                'rule'      => nl2br($optimizer->getRuleCondition()->getConditions()->asStringRecursive()),
                'rule_html' => $optimizer->getRuleCondition()->getConditions()->asHtmlRecursive(),
                'url'       => $this->urlBuilder->getUrl('smile_elasticsuite_catalog_optimizer/optimizer/edit', ['id' => $optimizer->getId()]),
            ];
        }

        if (isset($this->filters[$containerConfiguration->getName()])) {
            $optimizerIds = $this->filters[$containerConfiguration->getName()]->getOptimizerIds() ?? array_keys($results);
            $results      = array_intersect_key($results, array_flip($optimizerIds));
        }

        return array_values($results);
    }
}
