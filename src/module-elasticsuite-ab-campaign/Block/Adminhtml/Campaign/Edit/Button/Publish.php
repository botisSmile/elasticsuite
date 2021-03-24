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
 * Campaign publish button
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Publish extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $campaign = $this->getCampaign();
        if ($campaign && $campaign->getId() && $this->campaignManager->canPublish($campaign)) {
            $data = [
                'label' => __('Publish'),
                'class' => 'publish',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ). '\', \'' . $this->getPublishUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * Get publish url.
     *
     * @return string
     */
    private function getPublishUrl(): string
    {
        return $this->getUrl('*/*/publish', ['id' => $this->getCampaign()->getId()]);
    }
}
