<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\Rule;

/**
 * Refresh controller for virtual attribute rule : triggers the refreshing of a rule.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Refresh extends \Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ruleId         = (int) $this->getRequest()->getParam('id');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectParams = [];

        try {
            $rule = $this->ruleRepository->getById($ruleId);
            $this->ruleService->scheduleRefresh([$rule->getId()]);

            if ($redirectBack) {
                $redirectParams = ['id' => $ruleId];
            }

            $this->messageManager->addNoticeMessage(__('Rule has been scheduled for refreshment.'));

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while refreshing the rule.'));
            $resultRedirect->setPath('*/*/index');
        }

        return $resultRedirect->setPath($this->_redirect->getRefererUrl(), $redirectParams);
    }
}
