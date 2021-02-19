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
 * Mass action controller used to update rule status.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class MassStatus extends \Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\AbstractRule
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
        $status         = (int) $this->getRequest()->getParam('is_active');
        $message        = ($status === 0) ? 'A total of %1 rules(s) have been disabled.' : 'A total of %1 rules(s) have been enabled.';

        /** @var \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule */
        foreach ($collection as $rule) {
            $rule->setIsActive($status);
            $this->ruleRepository->save($rule);
        }

        $this->messageManager->addSuccessMessage(__($message, $collectionSize));

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
