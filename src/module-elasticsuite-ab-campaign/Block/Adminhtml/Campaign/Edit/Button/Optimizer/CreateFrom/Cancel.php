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

namespace Smile\ElasticsuiteAbCampaign\Block\Adminhtml\Campaign\Edit\Button\Optimizer\CreateFrom;

use Smile\ElasticsuiteCatalogOptimizer\Block\Adminhtml\Optimizer\Edit\Button\AbstractButton;

/**
 * Cancel button in the create optimizer from form.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Cancel extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Cancel'),
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            // Remove the create optimizer form from the modal.
                            [
                                'targetName' => 'smile_elasticsuite_ab_campaign_form'
                                    . '.smile_elasticsuite_ab_campaign_form.create_optimizer.create_optimizer_form',
                                'actionName' => 'destroyInserted',
                            ],
                            // Display optimizer list in the modal.
                            [
                                'targetName' => 'smile_elasticsuite_ab_campaign_form'
                                    . '.smile_elasticsuite_ab_campaign_form.create_optimizer'
                                    . '.smile_elasticsuite_ab_campaign_optimizer_listing',
                                'actionName' => 'setVisible',
                                'params' => [true],
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 20,
        ];
    }
}
