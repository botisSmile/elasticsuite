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
     * CampaignOptimizerActions constructor.
     *
     * @param ContextInterface   $context            Context
     * @param UiComponentFactory $uiComponentFactory Ui component factory
     * @param UrlInterface       $urlBuilder         Url builder
     * @param RequestInterface   $request            Request
     * @param array              $components         Components
     * @param array              $data               Data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['optimizer_id'])) {
                    $item[$name]['edit'] = [
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
                                    'id' => $item['optimizer_id'],
                                    'campaign_id' => $this->request->getParam('campaign_id'),
                                    'scenario_type' => $this->request->getParam('scenario_type'),
                                ],
                            ],
                        ],
                        'href' => '#',
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::OPTIMIZER_PATH_DELETE,
                            [
                                'id' => $item['optimizer_id'],
                                'campaign_id' => $this->request->getParam('campaign_id'),
                                'scenario_type' => $this->request->getParam('scenario_type'),
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
        }

        return $dataSource;
    }
}
