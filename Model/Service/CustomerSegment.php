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

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\Service;

use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\ResourceModel\Optimizer\CustomerSegment as CustomerSegmentResource;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Customer segment service.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 */
class CustomerSegment
{
    /**
     * @var \Magento\CustomerSegment\Helper\Data
     */
    private $customerSegmentHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerSegmentResource
     */
    private $customerSegmentResource;

    /**
     * @param \Magento\CustomerSegment\Helper\Data       $customerSegmentHelper   Customer segment helper.
     * @param \Magento\Framework\App\Http\Context        $httpContext             HTTP context.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager            Store manager.
     * @param CustomerSegmentResource                    $customerSegmentResource Optimizer customer segment resource.
     */
    public function __construct(
        \Magento\CustomerSegment\Helper\Data $customerSegmentHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerSegmentResource $customerSegmentResource
    ) {
        $this->customerSegmentHelper    = $customerSegmentHelper;
        $this->httpContext              = $httpContext;
        $this->storeManager             = $storeManager;
        $this->customerSegmentResource  = $customerSegmentResource;
    }

    /**
     * Returns the list of customer segment ids for the current customer/visitor.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @return array
     */
    public function getCurrentCustomerSegmentIds()
    {
        $segmentIds = [];

        if ($this->customerSegmentHelper->isEnabled()) {
            $segmentIds = $this->httpContext->getValue(\Magento\CustomerSegment\Helper\Data::CONTEXT_SEGMENT) ?? [];
        }

        return $segmentIds;
    }

    /**
     * Applies customer segments limitation to an optimizer collection for the current customer.
     *
     * @param OptimizerCollection $collection Optimizer collection.
     *
     * @return OptimizerCollection
     */
    public function applyCurrentCustomerSegmentsLimitation(OptimizerCollection $collection)
    {
        $segmentIds = $this->getCurrentCustomerSegmentIds();
        $allowedOptimizerIds = $this->customerSegmentResource->getApplicableOptimizerIdsByCustomerSegmentIds($segmentIds);

        $collection->addFieldToFilter('main_table.' . OptimizerInterface::OPTIMIZER_ID, ['in' => $allowedOptimizerIds]);

        return $collection;
    }
}
