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
use Magento\Framework\Exception\LocalizedException;
use Smile\ElasticsuiteAbCampaign\Controller\Adminhtml\AbstractAjaxOptimizer;

/**
 * Optimizer Ajax Delete Controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class AjaxDelete extends AbstractAjaxOptimizer
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $error = false;
        $errorMessages = [];
        $optimizerId = (int) $this->getRequest()->getParam('id');
        $scenarioType = (string) $this->getRequest()->getParam('scenario_type');
        if (!$this->validateScenarioTypeParam($scenarioType)) {
            throw new LocalizedException(__('Wrong scenario type.'));
        }

        $model = $this->getOptimizer();
        if (!$model || !$model->getId()) {
            $errorMessages[] = __('This optimizer no longer exists.');
            $error = true;

            return $this->sendJsonResult($error, $errorMessages, $optimizerId, $scenarioType);
        }

        try {
            $this->optimizerRepository->delete($model);
        } catch (Exception $e) {
            $error = true;
            $errorMessages[] = $e->getMessage();
        }

        return $this->sendJsonResult($error, $errorMessages, $optimizerId, $scenarioType);
    }
}
