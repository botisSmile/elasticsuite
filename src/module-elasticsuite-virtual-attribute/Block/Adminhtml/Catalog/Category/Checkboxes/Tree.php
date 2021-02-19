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
namespace Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Catalog\Category\Checkboxes;

/**
 * Smile Elastic Suite Virtual Attribute rules custom category chooser.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
{
    /**
     * {@inheritdoc}
     * Overridden to exclude virtual categories.
     */
    public function getCategoryCollection()
    {
        $collection = parent::getCategoryCollection();

        // Exclude virtual categories.
        $collection = $this->filterVirtualCategories($collection);

        // Overwrite collection already set in parent call.
        $this->setData('category_collection', $collection);

        return $collection;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Magento_Catalog::catalog/category/checkboxes/tree.phtml');
    }

    /**
     * Filter out virtual categories.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection Category Collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private function filterVirtualCategories(\Magento\Catalog\Model\ResourceModel\Category\Collection $collection)
    {
        $virtualCategoryAttribute = $collection->getResource()->getAttribute('is_virtual_category');

        $entity = $collection->getEntity();

        $collection->getSelect()->joinLeft(
            ['ccei' => $entity->getTable($virtualCategoryAttribute->getBackendTable())],
            sprintf(
                'e.%s = ccei.%s AND ccei.attribute_id = %s',
                $entity->getLinkField(),
                $entity->getLinkField(),
                (int) $virtualCategoryAttribute->getId()
            ),
            ['is_virtual_category' => 'value']
        )->where('ccei.value IS NULL OR ccei.value = 0');

        return $collection;
    }
}
