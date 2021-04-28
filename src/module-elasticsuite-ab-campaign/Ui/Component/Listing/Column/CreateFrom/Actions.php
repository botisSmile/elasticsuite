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

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Listing\Column\CreateFrom;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Create from optimizer Actions for Ui Component
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Actions extends Column
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Actions constructor.
     *
     * @param ContextInterface   $context            Context
     * @param UiComponentFactory $uiComponentFactory Ui component factory
     * @param RequestInterface   $request            Request
     * @param array              $components         Components
     * @param array              $data               Data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
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
                            // Hide the optimizer listing in the modal.
                            [
                                'provider' => 'smile_elasticsuite_ab_campaign_form.smile_elasticsuite_ab_campaign_form'
                                    . '.create_optimizer.smile_elasticsuite_ab_campaign_optimizer_listing',
                                'target' => 'setVisible',
                                'params' => false,
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
                                    'create_from' => 1,
                                ],
                            ],
                        ],
                        'href' => '#',
                        'label' => __('Choose'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
