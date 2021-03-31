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

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign\Optimizer;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer;

/**
 * Campaign Optimizer save handler.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var Optimizer
     */
    private $resource;

    /**
     * SaveHandler constructor.
     *
     * @param Optimizer $resource Resource
     */
    public function __construct(
        Optimizer $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        /** @var CampaignInterface $entity */
        $scenarioTypes = [CampaignOptimizerInterface::SCENARIO_TYPE_A, CampaignOptimizerInterface::SCENARIO_TYPE_B];
        foreach ($scenarioTypes as $scenarioType) {
            $optimizerIds = $entity->getOptimizerIds($scenarioType);
            $this->resource->saveCampaignOptimizer($entity->getId(), $optimizerIds, $scenarioType);
        }

        return $entity;
    }
}
