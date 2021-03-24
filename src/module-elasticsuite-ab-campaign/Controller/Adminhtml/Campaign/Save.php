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

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthBackendSession;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Framework\View\Result\PageFactory;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\AbstractCampaign as CampaignController;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\CompositeValidator;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Save Campaign controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Save extends CampaignController
{
    /**
     * @var CompositeValidator
     */
    protected $campaignValidator;

    /**
     * @var AuthBackendSession
     */
    protected $backendSession;

    /**
     * Save constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param Context                     $context            Context
     * @param PageFactory                 $resultPageFactory  Result page factory
     * @param CampaignContext             $campaignContext    Campaign context
     * @param CampaignRepositoryInterface $campaignRepository Campaign repository
     * @param CampaignInterfaceFactory    $campaignFactory    Campaign factory
     * @param DateFilter                  $dateFilter         Date filter
     * @param CampaignManagerInterface    $campaignManager    Campaign manager
     * @param CompositeValidator          $campaignValidator  Campaign validator
     * @param AuthBackendSession          $backendSession     Backend session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CampaignContext $campaignContext,
        CampaignRepositoryInterface $campaignRepository,
        CampaignInterfaceFactory $campaignFactory,
        DateFilter $dateFilter,
        CampaignManagerInterface $campaignManager,
        CompositeValidator $campaignValidator,
        AuthBackendSession $backendSession
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $campaignContext,
            $campaignRepository,
            $campaignFactory,
            $dateFilter,
            $campaignManager
        );
        $this->campaignValidator = $campaignValidator;
        $this->backendSession    = $backendSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        $redirectBack = $this->getRequest()->getParam('back', false);

        if ($data) {
            $identifier = (int) $this->getRequest()->getParam('campaign_id');
            /** @var Campaign $model */
            $model = $this->campaignFactory->create();

            if ($identifier) {
                $model = $this->campaignRepository->getById($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }

            $data = $this->formatAndCompleteData($data);

            try {
                $model->setData($data);
                $this->campaignValidator->validateData($model);
                $this->campaignRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the campaign %1.', $model->getName()));
                $this->preventForNoOptimizerSaved($identifier, $model);
                $this->backendSession->setFormData(false);

                if ($redirectBack) {
                    $redirectParams = ['id' => $model->getId()];

                    return $resultRedirect->setPath('*/*/edit', $redirectParams);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                if (!$data['campaign_id']) {
                    return $resultRedirect->setPath('*/*/create');
                }

                $returnParams = ['id' => $model->getId()];

                return $resultRedirect->setPath('*/*/edit', $returnParams);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Prevent customer if no optimizer was saved.
     *
     * @param int      $identifier Identifier
     * @param Campaign $model      Campaign
     * @return void
     */
    private function preventForNoOptimizerSaved($identifier, $model)
    {
        if ($identifier && !$model->getScenarioAOptimizerIds() && !$model->getScenarioBOptimizerIds()) {
            $this->messageManager->addWarningMessage(
                __('You saved no optimizer linked to the campaign %1.', $model->getName())
            );
        }
    }

    /**
     * Format and complete post data.
     *
     * @param array $data Data
     * @return mixed
     * @throws Exception
     */
    private function formatAndCompleteData($data)
    {
        $result = $data;
        if (!isset($data['campaign_id']) || !$data['campaign_id']) {
            $result['campaign_id'] = null;
            $result['author_id']   = $this->backendSession->getUser()->getId();
            $result['author_name'] = $this->backendSession->getUser()->getUserName();
            $result['status']      = Campaign::STATUS_DRAFT;
        }

        $result['start_date'] = isset($data['start_date']) ? $this->dateFilter->filter($data['start_date']) : null;
        $result['end_date'] = isset($data['end_date']) ? $this->dateFilter->filter($data['end_date']) : null;
        $result['scenario_a_optimizer_ids'] = $data['scenario_a_optimizer_ids'] ?? [];
        $result['scenario_b_optimizer_ids'] = $data['scenario_b_optimizer_ids'] ?? [];

        return $result;
    }
}
