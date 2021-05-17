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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Framework\View\Result\PageFactory;
use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\AbstractCampaign as CampaignController;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\CompositeValidator;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;
use Smile\ElasticsuiteCatalogOptimizer\Api\OptimizerRepositoryInterface;

/**
 * PersistOptimizers Campaign controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class PersistOptimizers extends CampaignController
{
    /**
     * @var CampaignOptimizerResource
     */
    protected $campaignOptimizerResource;

    /**
     * @var OptimizerRepositoryInterface
     */
    protected $optimizerRepository;

    /**
     * @var OptimizerManagerInterface
     */
    protected $optimizerManager;

    /**
     * PersistOptimizers constructor.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param Context                      $context                   Context
     * @param PageFactory                  $resultPageFactory         Result page factory
     * @param CampaignContext              $campaignContext           Campaign context
     * @param CampaignRepositoryInterface  $campaignRepository        Campaign repository
     * @param CampaignInterfaceFactory     $campaignFactory           Campaign factory
     * @param DateFilter                   $dateFilter                Date filter
     * @param CampaignManagerInterface     $campaignManager           Campaign manager
     * @param CompositeValidator           $campaignValidator         Campaign validator
     * @param CampaignOptimizerResource    $campaignOptimizerResource Campaign optimizer resource
     * @param OptimizerManagerInterface    $optimizerManager          Campaign Optimizer manager
     * @param OptimizerRepositoryInterface $optimizerRepository       Optimizer repository
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
        CampaignOptimizerResource $campaignOptimizerResource,
        OptimizerManagerInterface  $optimizerManager,
        OptimizerRepositoryInterface  $optimizerRepository
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $campaignContext,
            $campaignRepository,
            $campaignFactory,
            $dateFilter,
            $campaignManager,
            $campaignValidator
        );
        $this->campaignOptimizerResource = $campaignOptimizerResource;
        $this->optimizerRepository       = $optimizerRepository;
        $this->optimizerManager          = $optimizerManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $campaignId = (int) $this->getRequest()->getParam(CampaignOptimizerInterface::CAMPAIGN_ID);
        $scenarioType = (string) $this->getRequest()->getParam(CampaignOptimizerInterface::SCENARIO_TYPE);
        try {
            $campaign = $this->campaignRepository->getById($campaignId);
        } catch (NoSuchEntityException $noSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));

            return $resultRedirect->setPath('*/*/index');
        }

        if (!$this->validateScenarioTypeParam($scenarioType)) {
            $this->messageManager->addErrorMessage(__('The scenario does not exist.'));

            return $resultRedirect->setPath('*/*/edit', ['id' => $campaignId]);
        }

        $optimizerIds = $this->campaignOptimizerResource->getOptimizerIdsByCampaign($campaignId, $scenarioType);
        $optimizerIds = $optimizerIds ? explode(',', $optimizerIds[$scenarioType]) : [];
        if (!$optimizerIds) {
            $this->messageManager->addErrorMessage(__('There is no optimizers to publish.'));

            return $resultRedirect->setPath('*/*/edit', ['id' => $campaignId]);
        }

        $optimizerIdsSaved = [];
        foreach ($optimizerIds as $optimizerId) {
            try {
                $optimizer = $this->optimizerRepository->getById($optimizerId);
                $optimizer = $this->optimizerManager->addCampaignContextToOptimizer($campaign, $optimizer);
                $optimizer->setId(null)->setEntityId(null);
                $this->optimizerRepository->save($optimizer);
                $optimizerIdsSaved = $optimizer->getId();
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__('An error occurs when trying to publish optimizers.'));
                $this->rollbackOptimizers($optimizerIdsSaved);

                return $resultRedirect->setPath('*/*/edit', ['id' => $campaignId]);
            }
        }

        $this->messageManager->addSuccessMessage(__('You published well optimizers from scenario %1', $scenarioType));

        return $resultRedirect->setPath('*/*/edit', ['id' => $campaignId]);
    }

    /**
     * Rollback optimizers.
     *
     * @param array $optimizerIdsSaved Optimizer ids saved
     * @return void
     * @throws NoSuchEntityException
     * @throws InputException
     */
    private function rollbackOptimizers(array $optimizerIdsSaved): void
    {
        foreach ($optimizerIdsSaved as $optimizerId) {
            $this->optimizerRepository->deleteById($optimizerId);
        }
    }
}
