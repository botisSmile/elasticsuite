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
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

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
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface
     */
    protected $ruleService;

    /**
     * Abstract constructor.
     *
     * @param \Magento\Backend\App\Action\Context                                            $context           Application context.
     * @param \Magento\Framework\View\Result\PageFactory                                     $resultPageFactory Result Page factory.
     * @param \Magento\Framework\Registry                                                    $coreRegistry      Application registry.
     * @param \Magento\Framework\App\Request\DataPersistorInterface                          $dataPersistor     Data persistor.
     * @param \Magento\Ui\Component\MassAction\Filter                                        $filter            Mass Action Filter.
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $collectionFactory Collection Factory.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface                $ruleRepository    Rule Repository.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory              $ruleFactory       Rule Factory.
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface                   $ruleService       Rule Service.
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleRepositoryInterface $ruleRepository,
        \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterfaceFactory $ruleFactory,
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface $ruleService
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry      = $coreRegistry;
        $this->dataPersistor     = $dataPersistor;
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->ruleRepository    = $ruleRepository;
        $this->ruleFactory       = $ruleFactory;
        $this->ruleService       = $ruleService;
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
