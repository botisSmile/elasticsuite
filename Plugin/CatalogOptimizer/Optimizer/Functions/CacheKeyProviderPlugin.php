<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Plugin\CatalogOptimizer\Optimizer\Functions;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Functions\CacheKeyProviderInterface;
use Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\Service\CustomerSegment as CustomerSegmentService;

/**
 * Cache key provider plugin.
 * Used to vary the optimizers functions cache according to customer segments.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class CacheKeyProviderPlugin
{
    /**
     * @var CustomerSegmentService
     */
    private $segmentService;

    /**
     * CacheKeyProviderPlugin constructor.
     *
     * @param CustomerSegmentService $segmentService Customer segment service.
     */
    public function __construct(
        CustomerSegmentService $segmentService
    ) {
        $this->segmentService = $segmentService;
    }

    /**
     * Add the current customer/visitor customer segments to the cache key if applicable.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @param  CacheKeyProviderInterface $cacheKeyProvider Cache key provider.
     * @param  string                    $cacheKey         Current cache key.
     *
     * @return string
     */
    public function afterGetCacheKey(
        CacheKeyProviderInterface $cacheKeyProvider,
        $cacheKey
    ) {
        $segmentIds = $this->segmentService->getCurrentCustomerSegmentIds();
        if (!empty($segmentIds)) {
            $cacheKey = sprintf("%s_cs_%s", $cacheKey, implode('_', $segmentIds));
        }

        return $cacheKey;
    }
}
