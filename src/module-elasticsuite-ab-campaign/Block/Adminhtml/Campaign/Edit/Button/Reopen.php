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
 * Campaign reopen button
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Reopen extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $campaign = $this->getCampaign();
        if ($campaign && $campaign->getId() && $this->campaignManager->canReopen($campaign)) {
            $data = [
                'label' => __('Reopen'),
                'class' => 'reopen',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ). '\', \'' . $this->getReopenUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * Get reopen url.
     *
     * @return string
     */
    private function getReopenUrl(): string
    {
        return $this->getUrl('*/*/reopen', ['id' => $this->getCampaign()->getId()]);
    }
}
