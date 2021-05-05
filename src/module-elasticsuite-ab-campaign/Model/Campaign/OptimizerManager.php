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

namespace Smile\ElasticsuiteAbCampaign\Model\Campaign;

use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EventManager;
use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;

/**
 * Class OptimizerManager
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class OptimizerManager implements OptimizerManagerInterface
{
    /**
     * @var CampaignOptimizerResource
     */
    private $campaignOptimizerResource;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * OptimizerManager constructor.
     *
     * @param CampaignOptimizerResource $campaignOptimizerResource Campaign optimizer resource
     * @param EventManager              $eventManager              Event manager
     */
    public function __construct(
        CampaignOptimizerResource $campaignOptimizerResource,
        EventManager $eventManager
    ) {
        $this->campaignOptimizerResource = $campaignOptimizerResource;
        $this->eventManager              = $eventManager;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * {@inheritdoc}
     */
    public function extractOptimizerIdsToRestrain(
        array $optimizerIds,
        bool $takeInAccountCampaignStatus = false,
        bool $takeInAccountCampaignDates = false
    ): array {
        $filterByCampaignStatus = $takeInAccountCampaignStatus ? [CampaignInterface::STATUS_PUBLISHED] : [];

        return $this->campaignOptimizerResource->extractOptimizerIdsLinkedToCampaign(
            $optimizerIds,
            $filterByCampaignStatus,
            $takeInAccountCampaignDates
        );
    }

    /**
     * {@inheritDoc}
     */
    public function addCampaignContextToOptimizer(Campaign $campaign, $optimizer)
    {
        $optimizerContext = [];
        $optimizerContext['store_id']          = $campaign->getStoreId();
        $optimizerContext['search_containers'] = $campaign->getSearchContainers();
        $optimizerContext['search_container']  = array_keys($campaign->getSearchContainers());
        if ($campaign->getData('quick_search_container')) {
            $optimizerContext['quick_search_container'] = $campaign->getData('quick_search_container');
        }

        if ($campaign->getData('catalog_view_container')) {
            $optimizerContext['catalog_view_container'] = $campaign->getData('catalog_view_container');
        }

        $optimizerContextDataObject = new DataObject($optimizerContext);
        $this->eventManager->dispatch(
            'smile_elasticsuite_campaign_context_to_optimizer',
            [
                'campaign'          => $campaign,
                'optimizer_context' => $optimizerContextDataObject,
            ]
        );
        $optimizerContext = $optimizerContextDataObject->getData();

        if ($optimizer instanceof Optimizer) {
            $optimizer->addData($optimizerContext);

            return $optimizer;
        }

        return array_merge($optimizer, $optimizerContext);
    }
}
