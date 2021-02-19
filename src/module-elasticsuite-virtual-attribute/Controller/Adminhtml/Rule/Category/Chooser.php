<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
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
        $ids = $this->getRequest()->getParam('selected', []);

        if (!is_array($ids)) {
            $ids = [];
        }

        foreach ($ids as $key => &$id) {
            $id = (int) $id;
            if ($id <= 0) {
                unset($ids[$key]);
            }
        }

        return array_unique($ids);
    }
}
