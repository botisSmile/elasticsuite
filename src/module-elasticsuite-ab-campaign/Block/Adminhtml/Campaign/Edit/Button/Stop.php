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

namespace Smile\ElasticsuiteAbCampaign\Block\Adminhtml\Campaign\Edit\Button;

/**
 * Campaign stop button
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Stop extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $campaign = $this->getCampaign();
        if ($campaign && $campaign->getId() && $this->campaignManager->canStop($campaign)) {
            $data = [
                'label' => __('Stop'),
                'class' => 'stop',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ). '\', \'' . $this->getStopUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * Get stop url.
     *
     * @return string
     */
    private function getStopUrl(): string
    {
        return $this->getUrl('*/*/stop', ['id' => $this->getCampaign()->getId()]);
    }
}
