<?php
/**
 * DISCLAIMER
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

/**
 * Rule Adminhtml Index controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Save extends \Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getPostValue();
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $resultRedirect->setPath('*/*/');

        if ($data) {
            $identifier = $this->getRequest()->getParam(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::RULE_ID);
            $model      = $this->ruleFactory->create();

            if ($identifier) {
                $model = $this->ruleRepository->getById($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This rule no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }

            if (empty($data[\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::RULE_ID])) {
                $data[\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface::RULE_ID] = null;
            }

            $model->loadPost($data);

            try {
                $this->ruleRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the rule %1.', $model->getId()));
                $this->dataPersistor->clear('smile_elasticsuite_virtual_attribute_rule');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set('smile_elasticsuite_virtual_attribute_rule', $data);
            }

            if ($redirectBack && $model->getId()) {
                $redirectParams = ['id' => $model->getId()];
                $resultRedirect->setPath('*/*/edit', $redirectParams);
            }
        }

        return $resultRedirect;
    }
}
