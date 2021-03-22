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

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\AbstractCampaign as CampaignController;

/**
 * Edit Campaign controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Edit extends CampaignController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $campaignId = (int) $this->getRequest()->getParam('id');
        $campaign = null;

        try {
            $campaign = $this->campaignRepository->getById($campaignId);
            $this->campaignContext->setCurrentCampaign($campaign);
            $resultPage->getConfig()->getTitle()->prepend(__('Edit %1', $campaign->getName()));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the campaign.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');

            return $resultRedirect;
        }

        $resultPage->addBreadcrumb(__('Campaign'), __('Campaign'));

        return $resultPage;
    }
}
