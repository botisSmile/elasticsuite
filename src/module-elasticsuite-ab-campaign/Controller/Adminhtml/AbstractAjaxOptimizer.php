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

namespace Smile\ElasticsuiteAbCampaign\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterfaceFactory;
use Smile\ElasticsuiteCatalogOptimizer\Api\OptimizerRepositoryInterface;

/**
 * Abstract Ajax Optimizer Controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
abstract class AbstractAjaxOptimizer extends Action
{
    /**
     * @var OptimizerRepositoryInterface
     */
    protected $optimizerRepository;

    /**
     * @var OptimizerInterfaceFactory
     */
    protected $optimizerFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CampaignOptimizerResource
     */
    protected $campaignOptimizerResource;

    /**
     * @var OptimizerManagerInterface
     */
    protected $optimizerManager;

    /**
     * @var OptimizerInterface
     */
    protected $optimizer;

    /**
     * AbstractAjaxOptimizer constructor
     *
     * @param Context                      $context                   Context
     * @param OptimizerRepositoryInterface $optimizerRepository       Optimizer repository
     * @param OptimizerInterfaceFactory    $optimizerFactory          Optimizer factory
     * @param CampaignOptimizerResource    $campaignOptimizerResource Campaign optimizer resource
     * @param JsonFactory                  $resultJsonFactory         Result json factory
     * @param OptimizerManagerInterface    $optimizerManager          Optimizer manager
     */
    public function __construct(
        Context $context,
        OptimizerRepositoryInterface $optimizerRepository,
        OptimizerInterfaceFactory $optimizerFactory,
        CampaignOptimizerResource $campaignOptimizerResource,
        JsonFactory $resultJsonFactory,
        OptimizerManagerInterface $optimizerManager
    ) {
        parent::__construct($context);
        $this->optimizerRepository       = $optimizerRepository;
        $this->optimizerFactory          = $optimizerFactory;
        $this->campaignOptimizerResource = $campaignOptimizerResource;
        $this->resultJsonFactory         = $resultJsonFactory;
        $this->optimizerManager          = $optimizerManager;
    }

    /**
     * Send json result.
     *
     * @param bool   $error         Is in error ?
     * @param array  $errorMessages Error messages
     * @param int    $optimizerId   Optimizer id
     * @param string $scenarioType  Scenario type
     * @return Json
     */
    protected function sendJsonResult(
        bool $error,
        array $errorMessages,
        int $optimizerId,
        string $scenarioType
    ): Json {
        $resultJson = $this->resultJsonFactory->create();
        $data = [
            'error' => $error,
            'messages' => $errorMessages,
            'data' => [
                'optimizer_id'              => $optimizerId,
                'scenario_type'             => $scenarioType,
                'optimizer_ids_in_campaign' => $this->getOptimizerIdsInCampaign($scenarioType),
            ],
        ];
        $data = array_merge_recursive($data, $this->getAdditionalDataForJsonResult());

        return $resultJson->setData($data);
    }

    /**
     * Get optimizer from ajax request.
     *
     * @return OptimizerInterface|null
     */
    protected function getOptimizer(): ?OptimizerInterface
    {
        if ($this->optimizer === null) {
            // Retrieve the optimizer identifier/id.
            $identifier = (int) $this->getRequest()->getParam(OptimizerInterface::OPTIMIZER_ID);
            $identifier = $identifier ?: (int) $this->getRequest()->getParam('id');

            // Create new optimizer parameter to indicate if we will update or create the optimizer.
            $createNewOptimizer = (int) $this->getRequest()->getParam('create_new_optimizer');

            $model = $this->optimizerFactory->create();
            if ($identifier && !$createNewOptimizer) {
                try {
                    $model = $this->optimizerRepository->getById($identifier);
                } catch (NoSuchEntityException $noSuchEntityException) {
                    $model = null;
                }
            }

            $this->optimizer = $model;
        }

        return $this->optimizer;
    }

    /**
     * Validate scenario type param.
     *
     * @param string $scenarioType Scenario type
     * @return bool
     */
    protected function validateScenarioTypeParam(string $scenarioType): bool
    {
        return in_array(
            $scenarioType,
            [CampaignOptimizerInterface::SCENARIO_TYPE_A, CampaignOptimizerInterface::SCENARIO_TYPE_B]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Smile_ElasticsuiteAbCampaign::manage');
    }

    /**
     * Get additional data for json result.
     *
     * @return array
     */
    protected function getAdditionalDataForJsonResult(): array
    {
        return [];
    }

    /**
     * Get optimizer ids in campaign by scenario type.
     *
     * @param string $scenarioType Scenario type
     * @return array
     */
    private function getOptimizerIdsInCampaign(string $scenarioType): array
    {
        $campaignId = (int) $this->getRequest()->getParam('campaign_id');
        $optimizerIds = $this->campaignOptimizerResource->getOptimizerIdsByCampaign(
            $campaignId,
            $scenarioType
        );

        return $optimizerIds ? explode(',', $optimizerIds[$scenarioType]) : [];
    }
}
