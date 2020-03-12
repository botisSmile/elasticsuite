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
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Plugin\Framework\App\Action;

/**
 * Class ContextPlugin
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 */
class ContextPlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\CustomerSegment\Model\Customer
     */
    protected $customerSegment;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Customer\Model\Session            $customerSession Customer session.
     * @param \Magento\Framework\App\Http\Context        $httpContext     HTTP context.
     * @param \Magento\CustomerSegment\Model\Customer    $customerSegment Customer segment relationship model.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager    Store manager.
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\CustomerSegment\Model\Customer $customerSegment,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->customerSegment = $customerSegment;
        $this->storeManager = $storeManager;
    }

    /**
     * Around plugin. Adds the customer/visitor segment ids into the app http context.
     *
     * @param \Magento\Framework\App\ActionInterface  $subject Original app action.
     * @param \Closure                                $proceed Original AbstractAction::dispatch() method.
     * @param \Magento\Framework\App\RequestInterface $request App request.
     *
     * @return \Closure

     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($this->customerSession->getCustomerId()) {
            $customerSegmentIds = $this->customerSegment->getCustomerSegmentIdsForWebsite(
                $this->customerSession->getCustomerId(),
                $this->storeManager->getWebsite()->getId()
            );
            $this->httpContext->setValue(
                \Magento\CustomerSegment\Helper\Data::CONTEXT_SEGMENT,
                $customerSegmentIds,
                []
            );
        } else {
            $customerSegmentIds = [];
            $websiteId = (int) $this->storeManager->getWebsite()->getId();
            /*
             * No clean alternative for visitors due to inconsistency between 2.3.x versions
             * in \Magento\CustomerSegment\Model\Customer::getCurrentCustomerSegmentIds.
             */
            $allSegmentIds = $this->customerSession->getCustomerSegmentIds();
            if (is_array($allSegmentIds) && isset($allSegmentIds[$websiteId])) {
                $customerSegmentIds = $allSegmentIds[$websiteId];
            }
            $this->httpContext->setValue(
                \Magento\CustomerSegment\Helper\Data::CONTEXT_SEGMENT,
                $customerSegmentIds,
                []
            );
        }

        return $proceed($request);
    }
}
