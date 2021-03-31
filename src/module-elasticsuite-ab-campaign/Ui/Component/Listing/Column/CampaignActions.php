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

use Magento\Ui\Component\Listing\Columns\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

/**
 * Campaign Actions for Ui Component
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignActions extends Column
{
    /**
     * Edit Url path
     **/
    const OPTIMIZER_URL_PATH_EDIT = 'smile_elasticsuite_ab_campaign/campaign/edit';

    /**
     * Delete Url path
     **/
    const OPTIMIZER_URL_PATH_DELETE = 'smile_elasticsuite_ab_campaign/campaign/delete';

    /**
     * Stop Url path
     **/
    const OPTIMIZER_URL_PATH_STOP = 'smile_elasticsuite_ab_campaign/campaign/stop';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface   $context            Application context
     * @param UiComponentFactory $uiComponentFactory Ui Component Factory
     * @param UrlInterface       $urlBuilder         URL Builder
     * @param array              $components         Components
     * @param array              $data               Component Data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource The data source
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if (isset($item['campaign_id'])) {
                    $item[$name]['edit'] = [
                        'href'  => $this->urlBuilder->getUrl(self::OPTIMIZER_URL_PATH_EDIT, ['id' => $item['campaign_id']]),
                        'label' => __('Edit'),
                    ];

                    if ($item['status'] === CampaignInterface::STATUS_PUBLISHED) {
                        $item[$name]['stop'] = [
                            'href'    => $this->urlBuilder->getUrl(self::OPTIMIZER_URL_PATH_STOP, ['id' => $item['campaign_id']]),
                            'label'   => __('Stop'),
                            'confirm' => [
                                'title'   => __('Stop ${ $.$data.name }'),
                                'message' => __('Are you sure you want to stop ${ $.$data.name } ?'),
                            ],
                        ];
                    }

                    $item[$name]['delete'] = [
                        'href'    => $this->urlBuilder->getUrl(self::OPTIMIZER_URL_PATH_DELETE, ['id' => $item['campaign_id']]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete ${ $.$data.name }'),
                            'message' => __('Are you sure you want to delete ${ $.$data.name } ?'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
