<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Plugin\CatalogOptimizer\Optimizer\Collection;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Collection\ProviderInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\Service\CustomerSegment as CustomerSegmentService;

/**
 * Optimizer collection provider plugin.
 * Sets customer segment restrictions if applicable.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 */
class ProviderPlugin
{
    /**
     * @var CustomerSegmentService
     */
    private $segmentService;

    /**
     * ProviderPlugin constructor.
     *
     * @param CustomerSegmentService $segmentService Customer segment service.
     */
    public function __construct(
        CustomerSegmentService $segmentService
    ) {
        $this->segmentService = $segmentService;
    }

    /**
     * After plugin.
     * Adds current customer segment restriction if applicable.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ProviderInterface   $collectionProvider  Collection Provider.
     * @param OptimizerCollection $optimizerCollection Optimizer collection.
     *
     * @return OptimizerCollection
     */
    public function afterGetCollection(
        ProviderInterface $collectionProvider,
        OptimizerCollection $optimizerCollection
    ) {
        $optimizerCollection = $this->segmentService->applyCurrentCustomerSegmentsLimitation($optimizerCollection);

        return $optimizerCollection;
    }
}
