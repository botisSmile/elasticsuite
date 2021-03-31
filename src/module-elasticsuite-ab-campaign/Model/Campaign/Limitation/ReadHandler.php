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
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign as CampaignResource;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Limitation;

/**
 * Campaign Limitation read handler.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Limitation
     */
    private $resource;

    /**
     * @var CampaignResource
     */
    private $campaignResource;

    /**
     * Read handler constructor.
     *
     * @param Limitation       $resource         Resource
     * @param CampaignResource $campaignResource Campaign resource
     */
    public function __construct(
        Limitation $resource,
        CampaignResource $campaignResource
    ) {
        $this->resource = $resource;
        $this->campaignResource = $campaignResource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        /** @var Campaign $entity */
        if ($entity->getId()) {
            $searchContainers = $this->campaignResource->getSearchContainersFromCampaignId($entity->getId());
            $this->setCategoryLimitation($entity, $searchContainers);
            $this->setSearchQueryLimitation($entity, $searchContainers);
        }

        return $entity;
    }

    /**
     * Retrieve and set category ids limitation for the current campaign, if any.
     *
     * @param CampaignInterface $entity           The campaign being read
     * @param array             $searchContainers Search Containers data for the current campaign.
     * @return void
     */
    private function setCategoryLimitation(CampaignInterface $entity, array $searchContainers)
    {
        /** @var Campaign $entity */
        $applyTo = (bool) ($searchContainers['catalog_view_container'] ?? false);

        if ($applyTo) {
            $containerData = ['apply_to' => (int) true];
            $categoryIds   = $this->resource->getCategoryIdsByCampaign($entity);
            if (!empty($categoryIds)) {
                $containerData['category_ids'] = $categoryIds;
            }
            $entity->setData('catalog_view_container', $containerData);
        }
    }

    /**
     * Retrieve and set query ids limitation for the current campaign, if any.
     *
     * @param CampaignInterface $entity           The campaign being read
     * @param array             $searchContainers Search Containers data for the current campaign.
     * @return void
     */
    private function setSearchQueryLimitation(CampaignInterface $entity, array $searchContainers)
    {
        /** @var Campaign $entity */
        $applyTo = (bool) (
            $searchContainers['quick_search_container'] ?? ($searchContainers['catalog_product_autocomplete'] ?? false)
        );

        if ($applyTo) {
            $containerData = ['apply_to' => (int) true];
            $queryIds      = $this->resource->getQueryIdsByCampaign($entity);

            if (!empty($queryIds)) {
                $containerData['query_ids'] = $queryIds;
            }
            $entity->setData('quick_search_container', $containerData);
        }
    }
}
