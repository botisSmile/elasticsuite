<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Framework\View\Result\PageFactory;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\CompositeValidator;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Abstract Campaign controller
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
abstract class AbstractCampaign extends Action
{
    /**
     * @var PageFactory|null
     */
    protected $resultPageFactory = null;

    /**
     * @var CampaignContext
     */
    protected $campaignContext;

    /**
     * @var CampaignRepositoryInterface
     */
    protected $campaignRepository;

    /**
     * Campaign Factory
     *
     * @var CampaignInterfaceFactory
     */
    protected $campaignFactory;

    /**
     * @var DateFilter
     */
    protected $dateFilter;

    /**
     * @var CampaignManagerInterface
     */
    protected $campaignManager;

    /**
     * @var CompositeValidator
     */
    protected $campaignValidator;
    /**
     * Abstract constructor.
     *
     * @param Context                     $context            Application context.
     * @param PageFactory                 $resultPageFactory  Result Page factory.
     * @param CampaignContext             $campaignContext    Campaign Context.
     * @param CampaignRepositoryInterface $campaignRepository Campaign Repository.
     * @param CampaignInterfaceFactory    $campaignFactory    Campaign Factory.
     * @param DateFilter                  $dateFilter         Date filter
     * @param CampaignManagerInterface    $campaignManager    Campaign manager.
     * @param CompositeValidator          $campaignValidator  Campaign Validator.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CampaignContext $campaignContext,
        CampaignRepositoryInterface $campaignRepository,
        CampaignInterfaceFactory $campaignFactory,
        DateFilter $dateFilter,
        CampaignManagerInterface $campaignManager,
        CompositeValidator $campaignValidator
    ) {
        parent::__construct($context);

        $this->resultPageFactory   = $resultPageFactory;
        $this->campaignContext     = $campaignContext;
        $this->campaignRepository  = $campaignRepository;
        $this->campaignFactory     = $campaignFactory;
        $this->dateFilter          = $dateFilter;
        $this->campaignManager     = $campaignManager;
        $this->campaignValidator   = $campaignValidator;
    }

    /**
     * Create result page
     *
     * @return Page
     */
    protected function createPage()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage
            ->setActiveMenu('Smile_ElasticsuiteAbCampaign::manage_campaign')
            ->addBreadcrumb(__('Campaign'), __('Campaign'));

        return $resultPage;
    }

    /**
     * Validate scenario type param.
     *
     * @param string $scenarioType Scenario type
     * @return bool
     */
    protected function validateScenarioTypeParam(string $scenarioType): bool
    {
        return in_array(
            $scenarioType,
            [CampaignOptimizerInterface::SCENARIO_TYPE_A, CampaignOptimizerInterface::SCENARIO_TYPE_B]
        );
    }

    /**
     * Check if allowed to manage campaign.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Smile_ElasticsuiteAbCampaign::manage');
    }
}
