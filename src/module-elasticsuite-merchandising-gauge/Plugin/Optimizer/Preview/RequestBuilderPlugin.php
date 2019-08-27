<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Plugin\Optimizer\Preview;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Preview\RequestBuilder;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Smile\ElasticsuiteMerchandisingGauge\Search\Request\Product\Aggregation\Provider\MerchandisingMetricsProvider;
use Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationBuilder;

/**
 * Optimizer preview request builder plugin
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class RequestBuilderPlugin
{
    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var MerchandisingMetricsProvider
     */
    private $metricsProvider;

    /**
     * RequestBuilderPlugin constructor.
     *
     * @param AggregationBuilder           $aggregationBuilder Aggregation builder.
     * @param MerchandisingMetricsProvider $metricsProvider    Metrics provider.
     */
    public function __construct(
        AggregationBuilder $aggregationBuilder,
        MerchandisingMetricsProvider $metricsProvider
    ) {
        $this->aggregationBuilder = $aggregationBuilder;
        $this->metricsProvider = $metricsProvider;
    }

    /**
     * Around plugin - adds merchandising stats aggregations to the optimizer preview request.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param RequestBuilder                  $requestBuilder  Optimizer preview request builder.
     * @param \Closure                        $proceed         Original method.
     * @param ContainerConfigurationInterface $containerConfig Container configuration.
     * @param CategoryInterface               $category        The category.
     * @param string                          $queryText       The query text.
     * @param int                             $size            Query size.
     *
     * @return array
     */
    public function aroundGetSearchRequestParams(
        RequestBuilder $requestBuilder,
        \Closure $proceed,
        ContainerConfigurationInterface $containerConfig,
        $category = null,
        $queryText = null,
        $size = 20
    ) {
        $requestParams = $proceed($containerConfig, $category, $queryText, $size);

        $aggregations = $this->metricsProvider->getAggregations($containerConfig->getStoreId());
        $requestParams['buckets'] = array_merge(
            $requestParams['buckets'],
            $this->aggregationBuilder->buildAggregations($containerConfig, $aggregations, [])
        );

        return $requestParams;
    }
}
