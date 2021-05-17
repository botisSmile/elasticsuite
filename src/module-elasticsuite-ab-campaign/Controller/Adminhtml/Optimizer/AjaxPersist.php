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

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Optimizer Ajax Persist Controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class AjaxPersist extends AjaxSave
{
    /**
     * {@inheritDoc}
     */
    protected function formatData(array $data): array
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function afterSave(int $campaignId, int $optimizerId, string $scenarioType): void
    {
        // Do nothing.
    }

    /**
     * {@inheritDoc}
     */
    protected function getAdditionalDataForJsonResult(): array
    {
        return ['data' => ['persist' => (int) $this->getRequest()->getParam(OptimizerInterface::OPTIMIZER_ID)]];
    }
}
