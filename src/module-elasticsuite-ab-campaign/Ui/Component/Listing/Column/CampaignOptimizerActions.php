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

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Listing\Column;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Campaign Optimizer Actions for Ui Component
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignOptimizerActions extends Column
{
    const OPTIMIZER_PATH_DELETE = 'smile_elasticsuite_ab_campaign/optimizer/ajaxDelete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * CampaignOptimizerActions constructor.
     *
     * @param ContextInterface            $context            Context
     * @param UiComponentFactory          $uiComponentFactory Ui component factory
     * @param UrlInterface                $urlBuilder         Url builder
     * @param RequestInterface            $request            Request
     * @param CampaignRepositoryInterface $campaignRepository Campaign repository
     * @param array                       $components         Components
     * @param array                       $data               Data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        CampaignRepositoryInterface $campaignRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder         = $urlBuilder;
        $this->request            = $request;
        $this->campaignRepository = $campaignRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $campaignId = (int) $this->request->getParam('campaign_id');
            $scenarioType = (string) $this->request->getParam('scenario_type');
            if ($campaignId) {
                $campaign = $this->campaignRepository->getById($campaignId);
                $dataSource['data']['status'] = $campaign->getStatus();
                foreach ($dataSource['data']['items'] as &$item) {
                    $name = $this->getData('name');
                    if (isset($item['optimizer_id'])) {
                        $optimizerId = (int) $item['optimizer_id'];
                        if ($campaign->getStatus() === CampaignInterface::STATUS_COMPLETE) {
                            $item[$name]['persist'] = $this->getPersistAction($optimizerId, $campaignId, $scenarioType);
                            continue;
                        }

                        $item[$name]['edit'] = $this->getEditAction($optimizerId, $campaignId, $scenarioType);
                        $item[$name]['delete'] = $this->getDeleteAction($optimizerId, $campaignId, $scenarioType);
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get edit action.
     *
     * @param int    $optimizerId  Optimizer id
     * @param int    $campaignId   Campaign id
     * @param string $scenarioType Scenario type
     * @return array
     */
    private function getEditAction(int $optimizerId, int $campaignId, string $scenarioType): array
    {
        return [
            'callback' => [
                // Remove optimizer form from the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.create_optimizer_form',
                    'target' => 'destroyInserted',
                ],
                // Remove the optimizer listing from the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.smile_elasticsuite_ab_campaign_optimizer_listing',
                    'target' => 'destroyInserted',
                ],
                // Hide the insert listing uicomponent in the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.smile_elasticsuite_ab_campaign_optimizer_listing',
                    'target' => 'setVisible',
                    'params' => false,
                ],
                // Open the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer',
                    'target' => 'openModal',
                ],
                // Load the optimizer form in the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.create_optimizer_form',
                    'target' => 'render',
                    'params' => [
                        'id' => $optimizerId,
                        'campaign_id' => $campaignId,
                        'scenario_type' => $scenarioType,
                    ],
                ],
            ],
            'href' => '#',
            'label' => __('Edit'),
            'hidden' => false,
        ];
    }

    /**
     * Get persist action.
     *
     * @param int    $optimizerId  Optimizer id
     * @param int    $campaignId   Campaign id
     * @param string $scenarioType Scenario type
     * @return array
     */
    private function getPersistAction(int $optimizerId, int $campaignId, string $scenarioType): array
    {
        return [
            'callback' => [
                // Remove optimizer form from the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.create_optimizer_form',
                    'target' => 'destroyInserted',
                ],
                // Remove the optimizer listing from the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.smile_elasticsuite_ab_campaign_optimizer_listing',
                    'target' => 'destroyInserted',
                ],
                // Hide the insert listing uicomponent in the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.smile_elasticsuite_ab_campaign_optimizer_listing',
                    'target' => 'setVisible',
                    'params' => false,
                ],
                // Open the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer',
                    'target' => 'openModal',
                ],
                // Load the optimizer form in the modal.
                [
                    'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                        . '.create_optimizer.create_optimizer_form',
                    'target' => 'render',
                    'params' => [
                        'id' => $optimizerId,
                        'campaign_id' => $campaignId,
                        'scenario_type' => $scenarioType,
                        'persist' => true,
                    ],
                ],
            ],
            'href' => '#',
            'label' => __('Edit and publish'),
            'hidden' => false,
        ];
    }

    /**
     * Get delete action.
     *
     * @param int    $optimizerId  Optimizer id
     * @param int    $campaignId   Campaign id
     * @param string $scenarioType Scenario type
     * @return array
     */
    private function getDeleteAction(int $optimizerId, int $campaignId, string $scenarioType): array
    {
        return [
            'href' => $this->urlBuilder->getUrl(
                self::OPTIMIZER_PATH_DELETE,
                [
                    'id' => $optimizerId,
                    'campaign_id' => $campaignId,
                    'scenario_type' => $scenarioType,
                ]
            ),
            'label' => __('Delete'),
            'isAjax' => true,
            'confirm' => [
                'title' => __('Delete optimizer'),
                'message' => __('Are you sure you want to delete the optimizer?'),
            ],
        ];
    }
}
