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

namespace Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\Optimizer;

use Exception;
use Magento\Framework\DataObject;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\AbstractAjaxOptimizer;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterfaceFactory;

/**
 * Optimizer Ajax Save Controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class AjaxSave extends AbstractAjaxOptimizer
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $error = false;
        $errorMessages = [];
        $optimizerId = (int) $this->getRequest()->getParam(OptimizerInterface::OPTIMIZER_ID);
        $campaignId = (int) $this->getRequest()->getParam(CampaignOptimizerInterface::CAMPAIGN_ID);
        $scenarioType = (string) $this->getRequest()->getParam(CampaignOptimizerInterface::SCENARIO_TYPE);
        if (!$this->validateParams($campaignId, $scenarioType)) {
            $error = true;
            $errorMessages[] = __('Campaign Data are not correct to save optimizer');

            return $this->sendJsonResult($error, $errorMessages, $optimizerId, $scenarioType);
        }

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->getOptimizer();
            if (!$model) {
                $error = true;
                $errorMessages[] = __('This optimizer no longer exists.');

                return $this->sendJsonResult($error, $errorMessages, $optimizerId, $scenarioType);
            }

            // Unset optimizer id to create a new optimizer.
            if (empty($data['optimizer_id']) || $this->getRequest()->getParam('create_new_optimizer')) {
                $data['optimizer_id'] = null;
            }

            $data = $this->formatData($data);
            $validateResult = $model->validateData(new DataObject($data));
            if ($validateResult !== true) {
                $error = true;
                foreach ($validateResult as $errorMessage) {
                    $errorMessages[] = $errorMessage;
                }

                return $this->sendJsonResult($error, $errorMessages, $optimizerId, $scenarioType);
            }

            $model->setData($data);
            $ruleConditionPost = $this->getRequest()->getParam('rule_condition', []);
            $model->getRuleCondition()->loadPost($ruleConditionPost);

            try {
                $this->optimizerRepository->save($model);
                $optimizerId = $model->getId();
                $this->afterSave($campaignId, $optimizerId, $scenarioType);
            } catch (Exception $e) {
                $error = true;
                $errorMessages[] = $e->getMessage();
            }
        }

        return $this->sendJsonResult($error, $errorMessages, $optimizerId, $scenarioType);
    }

    /**
     * After save.
     *
     * @param int    $campaignId   Campaign id
     * @param int    $optimizerId  Optimizer id
     * @param string $scenarioType Scenario type
     * @return void
     */
    protected function afterSave(int $campaignId, int $optimizerId, string $scenarioType): void
    {
        $this->campaignOptimizerResource->saveCampaignOptimizerLink($campaignId, $optimizerId, $scenarioType);
    }

    /**
     * Format post data.
     *
     * @param array $data Data
     * @return array
     */
    protected function formatData(array $data): array
    {
        // Add empty data to avoid issues on campaign optimizer save.
        $data['search_container'] = [];
        $data['search_containers'] = [];

        return $data;
    }

    /**
     * Validate params: check that
     *  - campaign id is well defined
     *  - scenario type is either scenario A or scenario B
     *
     * @param int    $campaignId   Campaign Id
     * @param string $scenarioType Scenario type
     * @return bool
     */
    private function validateParams(int $campaignId, string $scenarioType): bool
    {
        return $campaignId && $this->validateScenarioTypeParam($scenarioType);
    }
}
