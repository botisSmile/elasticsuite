<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
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
