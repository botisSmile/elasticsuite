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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Flag\FlagResource;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignSessionRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionInterfaceFactory;
use Smile\ElasticsuiteAbCampaign\Helper\Significance as SignificanceHelper;
use Smile\ElasticsuiteAbCampaign\Model\CampaignAggregator\FlagFactory;
use Smile\ElasticsuiteAbCampaign\Model\CampaignAggregator\Flag;
use Smile\ElasticsuiteAbCampaign\Api\CampaignAggregatorInterface;
use Magento\Framework\Stdlib\DateTime as MagentoDateTime;
use Smile\ElasticsuiteAbCampaign\Model\Search\Campaign\KpiAggregator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Interface CampaignAggregator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class CampaignAggregator implements CampaignAggregatorInterface
{
    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var KpiAggregator
     */
    protected $kpiAggregator;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CampaignSessionInterfaceFactory
     */
    private $campaignSessionFactory;

    /**
     * @var CampaignSessionRepositoryInterface
     */
    private $campaignSessionRepository;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SignificanceHelper
     */
    protected $significanceHelper;

    /**
     * CampaignAggregator constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param FlagFactory                     $flagFactory               Flag factory
     * @param FlagResource                    $flagResource              Flag resource
     * @param KpiAggregator                   $kpiAggregator             Kpi aggregator
     * @param ScopeConfigInterface            $scopeConfig               Scope config
     * @param StoreManagerInterface           $storeManager              Store manager
     * @param CampaignSessionInterfaceFactory $campaignSessionFactory    Campaign session factory
     * @param CampaignSessionRepository       $campaignSessionRepository Campaign session repository
     * @param CampaignRepository              $campaignRepository        Campaign repository
     * @param SearchCriteriaBuilder           $searchCriteriaBuilder     Search criteria builder
     * @param LoggerInterface                 $logger                    Logger
     * @param SignificanceHelper              $significanceHelper        Significance helper
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource,
        KpiAggregator $kpiAggregator,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CampaignSessionInterfaceFactory $campaignSessionFactory,
        CampaignSessionRepository $campaignSessionRepository,
        CampaignRepository $campaignRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        SignificanceHelper $significanceHelper
    ) {
        $this->flagFactory  = $flagFactory;
        $this->flagResource = $flagResource;
        $this->scopeConfig  = $scopeConfig;
        $this->kpiAggregator = $kpiAggregator;
        $this->storeManager = $storeManager;
        $this->campaignSessionFactory = $campaignSessionFactory;
        $this->campaignSessionRepository = $campaignSessionRepository;
        $this->campaignRepository = $campaignRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->significanceHelper = $significanceHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateCampaignData(): void
    {
        $now = new \DateTime();
        /** @var Flag $flag */
        $flag = $this->flagFactory->create();
        $flag->loadSelf();
        $lastExecution = $flag->getFlagData()['last_execution'] ?? null;
        $lastExecution = $lastExecution !== null ? new \DateTime($lastExecution) : (new \DateTime())->setTimestamp(0);

        if ($flag->getState() ||
            $lastExecution->format(MagentoDateTime::DATE_PHP_FORMAT) === $now->format(MagentoDateTime::DATE_PHP_FORMAT)
        ) {
            // Campaign aggregator is running or already run today.
            return;
        }

        $flag->setState(1);
        $this->flagResource->save($flag);

        $visitCookieLifetime = $this->scopeConfig->getValue(
            'smile_elasticsuite_tracker/session/visit_cookie_lifetime'
        );
        $visitCookieLifetime = $visitCookieLifetime ?? 0;

        $daySeconds = 24 * 60 * 60;
        $shift = $visitCookieLifetime / $daySeconds > 1 ? $visitCookieLifetime % $daySeconds : $visitCookieLifetime;

        $today = new \DateTime();
        $today->setTime(0, 0);

        $thresholdDate = clone $today;
        $thresholdDate = $thresholdDate->modify(sprintf('+ %d seconds', $shift));
        if ($now < $thresholdDate) {
            // Skip because the sessions of the day before are not all expired.
            return;
        }

        $this->setRangeDate($lastExecution);
        if (!$this->setKpiData()) {
            $flag->setState(0);
            $this->flagResource->save($flag);

            return;
        }

        $this->saveCampaignSessions($this->data);
        $flag->setState(0);
        $flag->setFlagData([
            'last_execution' => (new \DateTime())->format(MagentoDateTime::DATETIME_PHP_FORMAT),
        ]);
        $this->flagResource->save($flag);
    }

    /**
     * Set Kpi data.
     *
     * @return bool
     */
    public function setKpiData(): bool
    {
        foreach ($this->storeManager->getStores() as $storeId => $store) {
            if (!isset($this->data[$storeId]) && $store->getIsActive()) {
                $this->kpiAggregator->setStoreId($storeId);
                try {
                    $this->data[$storeId] = $this->kpiAggregator->getData();
                } catch (\LogicException $e) {
                    $this->logger->error('Error during the campaign data aggregation', ['exception' => $e]);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Set range date.
     *
     * @param \DateTime $lastExecution Last execution
     */
    protected function setRangeDate(\DateTime $lastExecution): void
    {
        // Set from date.
        $yesterday = new \DateTime('yesterday');
        $fromDate = clone $lastExecution;
        if ($lastExecution->format(MagentoDateTime::DATE_PHP_FORMAT) === $yesterday->format(MagentoDateTime::DATE_PHP_FORMAT)
        ) {
            $fromDate = clone $yesterday;
        }

        $fromDate->setTime(0, 0);

        // Set to date.
        $toDate = clone $yesterday;
        $toDate->setTime(23, 59, 59);

        $this->kpiAggregator->setRangeDate(
            $fromDate->format(MagentoDateTime::DATETIME_PHP_FORMAT),
            $toDate->format(MagentoDateTime::DATETIME_PHP_FORMAT)
        );
    }
    /**
     * Save data in DB.
     *
     * @param array $campaignSessions Campaign sessions
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function saveCampaignSessions(array $campaignSessions): void
    {
        foreach ($campaignSessions as $campaignSessionsData) {
            foreach ($campaignSessionsData as $campaignId => $data) {
                try {
                    $this->campaignRepository->getById($campaignId);
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var CampaignSession|null $campaignSession */
                $campaignSession = $this->campaignSessionRepository->getByCampaignSessionData($campaignId);

                if ($campaignSession === null) {
                    $campaignSession = $this->campaignSessionFactory->create();
                }

                if ($campaignSession->getId() !== null) {
                    $data = $this->getCampaignSessionDataMerged($campaignSession, $data);
                }

                $campaignSession->setCampaignId($campaignId);
                $campaignSession->setSessionCountTotal($data['session_count']);

                $scenarioData = $data[CampaignOptimizerInterface::SCENARIO_TYPE_A];
                $campaignSession->setSessionCountA($scenarioData['session_count']);
                $campaignSession->setSalesCountA($scenarioData['sales_count']);
                $campaignSession->setConversionRateA((float) $scenarioData['conversion_rate']);

                $scenarioData = $data[CampaignOptimizerInterface::SCENARIO_TYPE_B];
                $campaignSession->setSessionCountB($scenarioData['session_count']);
                $campaignSession->setSalesCountB($scenarioData['sales_count']);
                $campaignSession->setConversionRateB((float) $scenarioData['conversion_rate']);

                $campaignSession->setSignificance(
                    $this->significanceHelper->isConfident(
                        $campaignSession->getSessionCountA(),
                        $campaignSession->getSalesCountA(),
                        $campaignSession->getSessionCountB(),
                        $campaignSession->getSalesCountB()
                    )
                );

                $this->campaignSessionRepository->save($campaignSession);
            }
        }
    }

    /**
     * Get campaign session  data merged.
     *
     * @param CampaignSessionInterface $campaignSession Campaign session
     * @param array                    $data            Data
     *
     * @return array
     */
    protected function getCampaignSessionDataMerged(CampaignSessionInterface $campaignSession, array $data): array
    {
        $scenarioTypeA = CampaignOptimizerInterface::SCENARIO_TYPE_A;
        $scenarioTypeB = CampaignOptimizerInterface::SCENARIO_TYPE_B;
        $campaignSession->getSessionCountTotal();
        $data['session_count'] += $campaignSession->getSessionCountTotal();

        $data[$scenarioTypeA]['session_count'] += $campaignSession->getSessionCountA();
        $data[$scenarioTypeA]['sales_count'] += $campaignSession->getSalesCountA();
        $data[$scenarioTypeA]['conversion_rate'] = number_format(
            $data[$scenarioTypeA]['sales_count'] / $data[$scenarioTypeA]['session_count'],
            2
        );

        $data[$scenarioTypeB]['session_count'] += $campaignSession->getSessionCountB();
        $data[$scenarioTypeB]['sales_count'] += $campaignSession->getSalesCountB();
        $data[$scenarioTypeB]['conversion_rate'] = number_format(
            $data[$scenarioTypeB]['sales_count'] / $data[$scenarioTypeB]['session_count'],
            2
        );

        return $data;
    }
}
