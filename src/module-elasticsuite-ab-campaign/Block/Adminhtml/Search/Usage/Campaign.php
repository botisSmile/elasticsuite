<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteAbCampaign\Block\Adminhtml\Search\Usage;

use \Magento\Backend\Block\Template;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Model\CampaignRepository;
use Smile\ElasticsuiteAbCampaign\Helper\Data as AbCampaignHelper;
use Magento\Backend\Block\Template\Context;
use Smile\ElasticsuiteAbCampaign\Model\Search\Usage\Campaign\Report;

/**
 * Block used to display KPIs in the search usage dashboard.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class Campaign extends Template
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AbCampaignHelper
     */
    protected $abCampaignHelper;

    /**
     * Campaign constructor.
     *
     * @param Context               $context               Context
     * @param Report                $report                Report model
     * @param CampaignRepository    $campaignRepository    Campaign repository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Search criteria builder
     * @param AbCampaignHelper      $abCampaignHelper      AB campaign helper
     * @param array                 $data                  Data
     */
    public function __construct(
        Context $context,
        Report $report,
        CampaignRepository $campaignRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AbCampaignHelper $abCampaignHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->report = $report;
        $this->campaignRepository = $campaignRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->abCampaignHelper = $abCampaignHelper;
    }

    /**
     * Get campaign sessions data.
     *
     * @return array
     */
    public function getCampaignSessionsData(): array
    {
        $campaignsData = [];

        try {
            $campaigns = $this->report->getData();
            if (empty($campaigns)) {
                return $campaignsData;
            }
            ksort($campaigns);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(CampaignInterface::CAMPAIGN_ID, array_keys($campaigns), 'in')
                ->create();

            $campaignsDb = $this->campaignRepository->getList($searchCriteria)->getItems();
            foreach ($campaignsDb as $campaignDb) {
                $campaigns[$campaignDb->getId()]['name'] = $campaignDb->getName();
                $campaigns[$campaignDb->getId()]['url'] = $this->getUrl(
                    'smile_elasticsuite_ab_campaign/campaign/edit',
                    ['_current' => true, 'id' => $campaignDb->getId()]
                );
            }

            foreach ($campaigns as $id => $campaign) {
                $campaignsData[$id] = [
                    'campaign_id' => $id,
                    'name' => $campaign['name'] ?? $id,
                    'url' => $campaign['url'] ?? null,
                    'scenario_session_a' => $campaign[CampaignOptimizerInterface::SCENARIO_TYPE_A]['session_count'],
                    'scenario_session_b' => $campaign[CampaignOptimizerInterface::SCENARIO_TYPE_B]['session_count'],
                    'conversion_rate_a' => $this->abCampaignHelper->formatInPercentage(
                        $campaign[CampaignOptimizerInterface::SCENARIO_TYPE_A]['conversion_rate']
                    ),
                    'conversion_rate_b' => $this->abCampaignHelper->formatInPercentage(
                        $campaign[CampaignOptimizerInterface::SCENARIO_TYPE_B]['conversion_rate']
                    ),
                ];
            }
        } catch (\LogicException $e) {
            $this->_logger->critical('Error during the rendering of AB testing campaign data', ['exception' => $e]);
        }

        return $campaignsData;
    }
}
