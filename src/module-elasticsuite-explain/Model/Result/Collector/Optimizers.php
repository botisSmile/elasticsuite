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
use Smile\ElasticsuiteBehavioralOptimizer\Ui\Component\Optimizer\Source\Config\BehavioralData\Options;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

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
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var array behavioral data option-values
     */
    private $options = [];

    /**
     * @var array
     */
    private $typeLabels = [];

    /**
     * Optimizers constructor.
     *
     * @param ProviderInterface                   $provider                   Optimizers Provider
     * @param UrlInterface                        $urlBuilder                 URL builder.
     * @param Options                             $optionsSource              Behavioral Optimizers option source.
     * @param ProductAttributeRepositoryInterface $productAttributeRepository Attribute Repository
     * @param OptimizerFilterInterface[]          $filters                    Optimizer filters.
     */
    public function __construct(
        ProviderInterface $provider,
        UrlInterface $urlBuilder,
        Options $optionsSource,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        $filters = []
    ) {
        $this->provider            = $provider;
        $this->urlBuilder          = $urlBuilder;
        $this->filters             = $filters;
        $this->attributeRepository = $productAttributeRepository;
        $this->options             = $optionsSource->toOptionArray();
        $this->typeLabels          = [
            'constant_score'  => 'Constant Score',
            'attribute_value' => 'Based on attribute value',
            'behavioral'      => 'Based on behavioral data',
        ];
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
                'type'      => $this->getType($optimizer),
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
        if ($optimizer->getConfig('constant_score_value')) {
            $result = $optimizer->getConfig('constant_score_value') . '%';
        } elseif ($optimizer->getConfig('scale_factor')) {
            $scaleFactor = $optimizer->getConfig('scale_factor');
            if ($optimizer->getConfig('attribute_code')) {
                try {
                    $attribute = $this->getAttribute($optimizer->getConfig('attribute_code'));
                    $field     = $attribute->getDefaultFrontendLabel();
                } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                    $field = $optimizer->getConfig('attribute_code');
                }
            } elseif ($optimizer->getConfig('metric')) {
                $field = $optimizer->getConfig('metric');
                if (isset($this->options[$field]) && isset($this->options[$field]['label'])) {
                    $field = $this->options[$field]['label'] ?? $field;
                }
            }
            $result = __(sprintf('%s * %s', $scaleFactor, $field));
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
            $result = sprintf("1 + (%s / 100)", (float) $optimizer->getConfig('constant_score_value'));
        } elseif ($optimizer->getConfig('scale_factor')) {
            $factor   = $optimizer->getConfig('scale_factor') ?? '';
            $modifier = $optimizer->getConfig('scale_function') ?? '';
            $field    = $optimizer->getConfig('attribute_code') ?? $optimizer->getConfig('metric') ?? '';
            $result   = sprintf("%s(%s * %s)", $modifier, $factor, $field);
        }

        return $result;
    }

    /**
     * Get type for a given optimizer.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface $optimizer The optimizer
     *
     * @return string
     */
    private function getType(OptimizerInterface $optimizer)
    {
        $model = $optimizer->getModel();

        return $this->typeLabels[$model] ?? $model ?? '';
    }

    /**
     * Get attribute by attribute code.
     *
     * @param string $attributeCode The attribute code
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }
}
