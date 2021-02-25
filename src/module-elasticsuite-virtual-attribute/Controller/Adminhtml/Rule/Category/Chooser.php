<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\Rule\Category;

/**
 * Smile Elastic Suite Virtual Attribute Category Chooser controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Chooser extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Smile_ElasticsuiteVirtualAttribute::manage';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $block = $this->_view->getLayout()->createBlock(
            \Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Catalog\Category\Checkboxes\Tree::class,
            'promo_widget_chooser_category_ids',
            [
                'data' => ['js_form_object' => $this->getRequest()->getParam('form')],
            ]
        )->setCategoryIds(
            $this->getIds()
        );

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Retrieve currently selected Ids.
     *
     * @return array
     */
    private function getIds()
    {
        $categoryIds = $this->getRequest()->getParam('selected', []);

        if (!is_array($categoryIds)) {
            $categoryIds = [];
        }

        foreach ($categoryIds as $key => &$categoryId) {
            $categoryId = (int) $categoryId;
            if ($categoryIds <= 0) {
                unset($categoryIds[$key]);
            }
        }

        return array_unique($categoryIds);
    }
}
