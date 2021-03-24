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
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign as CampaignResource;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer;

/**
 * Campaign Optimizer read handler.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Optimizer
     */
    private $resource;

    /**
     * Read handler constructor.
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
        /** @var Campaign $entity */
        if ($entity->getId()) {
            $optimizerIdsByScenario = $this->resource->getOptimizerIdsByCampaign($entity->getId());
            foreach ($optimizerIdsByScenario as $scenarioType => $optimizerIds) {
                $entity->setOptimizerIds(explode(',', $optimizerIds), $scenarioType);
            }
        }

        return $entity;
    }
}
