<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteExplain\Controller\Adminhtml\Explain;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Explain Adminhtml Index controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Index extends Action
{
    /**
     * {@inheritDoc}
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Smile_ElasticsuiteExplain::explain')
                   ->addBreadcrumb(__('Explain'), __('Explain'));

        $resultPage->getConfig()->getTitle()->prepend(__('Explain search and navigation results'));
        $resultPage->addBreadcrumb(__('Explain'), __('Explain'));

        return $resultPage;
    }
}
