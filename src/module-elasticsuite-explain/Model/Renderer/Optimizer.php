<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Renderer;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\UrlInterface;
use Smile\ElasticsuiteBehavioralOptimizer\Ui\Component\Optimizer\Source\Config\BehavioralData\Options;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\OptimizerFilterInterface;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Collection\ProviderInterface;

/**
 * Optimizer Renderer.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Optimizer
{
    /**
     * Optimizer constructor.
     *
     * @param Options                             $optionsSource              Behavioral Data options source
     * @param ProductAttributeRepositoryInterface $productAttributeRepository Attribute Repository
     */
    public function __construct(
        Options $optionsSource,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->attributeRepository = $productAttributeRepository;
        $this->options             = $optionsSource->toOptionArray();
        $this->typeLabels          = [
            'constant_score'  => 'Constant Score',
            'attribute_value' => 'Based on attribute value',
            'behavioral'      => 'Based on behavioral data',
        ];
    }

    /**
     * Get boost of a given optimizer.
     *
     * @param OptimizerInterface $optimizer The optimizer
     * @return string
     */
    public function renderBoost(OptimizerInterface $optimizer)
    {
        $result = null;

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
     * Render rule conditions.
     *
     * @param OptimizerInterface $optimizer Optimizer
     * @return string
     */
    public function renderRuleConditions(OptimizerInterface $optimizer)
    {
        return nl2br($optimizer->getRuleCondition()->getConditions()->asStringRecursive());
    }

    /**
     * Get tooltip for a given optimizer.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface $optimizer The optimizer
     *
     * @return string
     */
    public function renderTooltip(OptimizerInterface $optimizer)
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
    public function renderType(OptimizerInterface $optimizer)
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
