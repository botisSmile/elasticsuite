<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\Campaign;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\AbstractCampaign as CampaignController;

/**
 * Delete Campaign controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Delete extends CampaignController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $identifier = (int) $this->getRequest()->getParam('id', false);
        /** @var CampaignInterface $model */
        $model = $this->campaignFactory->create();
        if ($identifier) {
            try {
                $model = $this->campaignRepository->getById($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));

                    return $resultRedirect->setPath('*/*/index');
                }
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));

                return $resultRedirect->setPath('*/*/index');
            }
        }

        try {
            $this->campaignRepository->delete($model);
            $this->messageManager->addSuccessMessage(__('You deleted the campaign %1.', $model->getName()));

            return $resultRedirect->setPath('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
    }
}
