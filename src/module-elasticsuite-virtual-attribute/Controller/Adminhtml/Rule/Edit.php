<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\Rule;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Rule Adminhtml Index controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Edit extends \Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $ruleId = (int) $this->getRequest()->getParam('id');
        $rule   = null;

        try {
            $rule = $this->ruleRepository->getById($ruleId);
            $this->coreRegistry->register('current_rule', $rule);
            $resultPage->getConfig()->getTitle()->prepend(__('Edit rule %1', $rule->getId()));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the rule.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');

            return $resultRedirect;
        }

        $resultPage->addBreadcrumb(__('Rule'), __('Rule'));

        return $resultPage;
    }
}
