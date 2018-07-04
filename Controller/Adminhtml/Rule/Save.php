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
            $identifier = $this->getRequest()->getParam('id');
            $model = $this->ruleFactory->create();

            if ($identifier) {
                $model = $this->ruleRepository->getById($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This rule no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }

            if (empty($data['rule_id'])) {
                $data['rule_id'] = null;
            }

            $model->setData($data);
            $ruleConditionPost = $this->getRequest()->getParam('condition', []);
            $model->getCondition()->loadPost($ruleConditionPost);

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
