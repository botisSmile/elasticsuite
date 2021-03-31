<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Campaign\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Limitation as LimitationResource;

/**
 * Campaign Ui Component Modifier. Used to populate search queries dynamicRows.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Limitation implements ModifierInterface
{
    /**
     * @var CampaignContext
     */
    private $campaignContext;

    /**
     * @var LimitationResource
     */
    private $resource;

    /**
     * Limitation constructor.
     *
     * @param CampaignContext    $campaignContext Campaign context
     * @param LimitationResource $resource        Limitation Resource
     */
    public function __construct(
        CampaignContext $campaignContext,
        LimitationResource $resource
    ) {
        $this->campaignContext = $campaignContext;
        $this->resource        = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $campaign = $this->campaignContext->getCurrentCampaign();

        if ($campaign && $campaign->getId() && isset($data[$campaign->getId()])) {
            $searchContainers = $campaign->getSearchContainers();

            $data[$campaign->getId()]['search_container'] = array_keys($searchContainers);

            $applyToCategories = (bool) ($searchContainers['catalog_view_container'] ?? false);
            if ($applyToCategories) {
                $containerData = ['apply_to' => 1];
                $categoryIds = $this->resource->getCategoryIdsByCampaign($campaign);
                if (!empty($categoryIds)) {
                    $containerData['category_ids'] = $categoryIds;
                }
                $data[$campaign->getId()]['catalog_view_container'] = $containerData;
            }

            // @codingStandardsIgnoreStart
            $applyToQueries = (bool)($searchContainers['quick_search_container']
                ?? ($searchContainers['catalog_product_autocomplete'] ?? false));
            // @codingStandardsIgnoreEnd

            if ($applyToQueries) {
                $containerData = ['apply_to' => 1];
                $queryIds = $this->resource->getQueryIdsByCampaign($campaign);
                if (!empty($queryIds)) {
                    $containerData['query_ids'] = $queryIds;
                }
                $data[$campaign->getId()]['quick_search_container'] = $containerData;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
