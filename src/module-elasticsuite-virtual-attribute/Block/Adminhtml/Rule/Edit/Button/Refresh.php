<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Rule\Edit\Button;

/**
 * Refresh Button for rule edition
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Refresh extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getRule() && $this->getRule()->getId()) {
            $data = [
                'label' => __('Refresh'),
                'class' => 'action-secondary',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ). '\', \'' . $this->getRefreshUrl() . '\')',
                'sort_order' => 100,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getRefreshUrl()
    {
        return $this->getUrl('*/*/refresh', ['id' => $this->getRule()->getId(), 'back' => 1]);
    }
}
