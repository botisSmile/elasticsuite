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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
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
                'boost'     => $this->getBoost($optimizer),
                'tooltip'   => $this->getTooltip($optimizer),
                'rule'      => nl2br($optimizer->getRuleCondition()->getConditions()->asStringRecursive()),
                'rule_html' => $optimizer->getRuleCondition()->getConditions()->asHtmlRecursive(),
                'url'       => $this->urlBuilder->getUrl(
                    'smile_elasticsuite_catalog_optimizer/optimizer/edit',
                    ['id' => $optimizer->getId()]
                ),
            ];
        }

        if (isset($this->filters[$containerConfiguration->getName()])) {
            $optimizerIds = $this->filters[$containerConfiguration->getName()]->getOptimizerIds() ?? array_keys($results);
            $results      = array_intersect_key($results, array_flip($optimizerIds));
        }

        return array_values($results);
    }

    /**
     * Get boost of a given optimizer.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface $optimizer The optimizer
     *
     * @return string
     */
    private function getBoost(OptimizerInterface $optimizer)
    {
        $result = null;

        if ($optimizer->getConfig('constant_score_value')) {
            $result = $optimizer->getConfig('constant_score_value') . '%';
        } elseif ($optimizer->getConfig('scale_factor')) {
            $field    = $optimizer->getConfig('attribute_code') ?? $optimizer->getConfig('metric') ?? '';
            $result   = __(sprintf('Proportional to "%s"', $field));
        }

        return $result;
    }

    /**
     * Get tooltip for a given optimizer.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface $optimizer The optimizer
     *
     * @return string
     */
    private function getTooltip(OptimizerInterface $optimizer)
    {
        $result = null;

        if ($optimizer->getConfig('constant_score_value')) {
            $result   = sprintf("1 + (%s / 100)", (float) $optimizer->getConfig('constant_score_value'));
        } elseif ($optimizer->getConfig('scale_factor')) {
            $factor   = $optimizer->getConfig('scale_factor') ?? '';
            $modifier = $optimizer->getConfig('scale_function') ?? '';
            $field    = $optimizer->getConfig('attribute_code') ?? $optimizer->getConfig('metric') ?? '';
            $result   = sprintf("%s(%s * %s)", $modifier, $factor, $field);
        }

        return $result;
    }
}
