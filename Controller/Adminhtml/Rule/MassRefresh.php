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
namespace Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\Rule;

/**
 * Smile Elastic Suite Virtual Attribute refresh mass action controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class MassRefresh extends \Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\AbstractRule
{
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $this->ruleService->refresh($collection->getAllIds());

        $this->messageManager->addSuccessMessage(__('A total of %1 rules(s) have been set for refreshment.', $collectionSize));

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
