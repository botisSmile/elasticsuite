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

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign\Limitation;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Limitation;

/**
 * Campaign Limitation save handler.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var Limitation
     */
    private $resource;

    /**
     * SearchTermsSaveHandler constructor.
     *
     * @param Limitation $resource Resource
     */
    public function __construct(
        Limitation $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        /** @var Campaign $entity */
        $categoryIds = $this->getCategoryIdsLimitation($entity);
        $queryIds    = $this->getQueryIdsLimitation($entity);

        $limitationData = [];

        foreach ($categoryIds as $categoryId) {
            $limitationData[] = ['category_id' => $categoryId];
        }

        foreach ($queryIds as $queryId) {
            $limitationData[] = ['query_id' => $queryId];
        }

        $this->resource->saveLimitation($entity, $limitationData);

        return $entity;
    }

    /**
     * Retrieve category ids limitation for the current campaign, if any.
     *
     * @param CampaignInterface $entity The campaign being saved
     * @return array
     */
    private function getCategoryIdsLimitation(CampaignInterface $entity)
    {
        /** @var Campaign $entity */
        $searchContainerData = $entity->getData('catalog_view_container');
        $applyTo     = is_array($searchContainerData) ? ((bool) $searchContainerData['apply_to'] ?? false) : false;

        return ($applyTo === false) ? [] : $searchContainerData['category_ids'] ?? [];
    }

    /**
     * Retrieve query ids limitation for the current campaign, if any.
     *
     * @param CampaignInterface $entity The campaign being saved
     * @return array
     */
    private function getQueryIdsLimitation(CampaignInterface $entity)
    {
        /** @var Campaign $entity */
        $searchContainerData = $entity->getData('quick_search_container');
        $applyTo  = is_array($searchContainerData) ? ((bool) $searchContainerData['apply_to'] ?? false) : false;
        $queryIds = [];

        if (($applyTo !== false) && (isset($searchContainerData['query_ids']) && !empty($searchContainerData['query_ids']))) {
            $ids = $queryIds = $searchContainerData['query_ids'];

            if (is_array(current($ids))) {
                $queryIds = [];
                foreach ($ids as $queryId) {
                    if (isset($queryId['id'])) {
                        $queryIds[] = (int) $queryId['id'];
                    }
                }
            }
        }

        return $queryIds;
    }
}
