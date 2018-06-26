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
namespace Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Abstract Rule controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
abstract class AbstractRule extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Smile_ElasticsuiteVirtualAttribute::manage';

    /**
     * @var \Magento\Framework\View\Result\PageFactory|null
     */
    protected $resultPageFactory = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * Rule Factory
     *
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory
     */
    protected $ruleFactory;

    /**
     * Abstract constructor.
     *
     * @param \Magento\Backend\App\Action\Context                                    $context             Application context.
     * @param \Magento\Framework\View\Result\PageFactory                             $resultPageFactory   Result Page factory.
     * @param \Magento\Framework\Registry                                            $coreRegistry        Application registry.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface   $ruleRepository Rule Repository.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory $ruleFactory    Rule Factory.
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface $ruleRepository,
        \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory $ruleFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry      = $coreRegistry;
        $this->ruleRepository    = $ruleRepository;
        $this->ruleFactory       = $ruleFactory;
    }

    /**
     * Create result page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Smile_ElasticsuiteVirtualAttribute::rule')->addBreadcrumb(__('Rule'), __('Rule'));

        return $resultPage;
    }
}
