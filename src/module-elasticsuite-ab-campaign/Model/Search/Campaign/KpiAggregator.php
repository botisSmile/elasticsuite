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

namespace Smile\ElasticsuiteAbCampaign\Model\Search\Campaign;

use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteAnalytics\Model\Report\QueryProviderInterface;
use Smile\ElasticsuiteCore\Search\RequestInterface;
use Smile\ElasticsuiteAbCampaign\Model\Search\Campaign\DateFilterQueryProvider;

/**
 * Class KpiAggregator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class KpiAggregator
{
    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    private $searchEngine;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Builder
     */
    private $searchRequestBuilder;

    /**
     * @var \Smile\ElasticsuiteAnalytics\Model\Report\Context
     */
    private $context;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory
     */
    private $aggregationFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    private $queryFactory;

    /**
     * @var QueryProviderInterface[]
     */
    private $queryProviders;

    /**
     * @var array
     */
    private $postProcessors;

    /**
     * @var string
     */
    private $containerName;

    /**
     * @var integer| null
     */
    private $storeId;

    /**
     * KpiAggregator constructor.
     *
     * @param \Magento\Search\Model\SearchEngine                                    $searchEngine         SearchEngine
     * @param \Smile\ElasticsuiteCore\Search\Request\Builder                        $searchRequestBuilder Search request builder
     * @param \Smile\ElasticsuiteAnalytics\Model\Report\Context                     $context              Context
     * @param \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory $aggregationFactory   Aggregation factory
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory             $queryFactory         QueryFactory
     * @param array                                                                 $postProcessors       Post Processors
     * @param array                                                                 $queryProviders       Query Providers
     * @param string                                                                $containerName        Container Name
     */
    public function __construct(
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Smile\ElasticsuiteCore\Search\Request\Builder $searchRequestBuilder,
        \Smile\ElasticsuiteAnalytics\Model\Report\Context $context,
        \Smile\ElasticsuiteCore\Search\Request\Aggregation\AggregationFactory $aggregationFactory,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        array $postProcessors = [],
        array $queryProviders = [],
        $containerName = 'tracking_log_session'
    ) {
        $this->searchEngine         = $searchEngine;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->context              = $context;
        $this->aggregationFactory   = $aggregationFactory;
        $this->queryFactory         = $queryFactory;
        $this->queryProviders       = $queryProviders;
        $this->postProcessors       = $postProcessors;
        $this->containerName        = $containerName;
    }

    /**
     * Set Range date.
     *
     * @param string $fromDate From date
     * @param string $toData   To date
     */
    public function setRangeDate(string $fromDate, string $toData): void
    {
        foreach ($this->queryProviders as $queryProvider) {
            if ($queryProvider instanceof DateFilterQueryProvider) {
                $queryProvider->setRange($fromDate, $toData);
            }
        }
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return $this->storeId;
    }

    /**
     * @param int $storeId Store id
     */
    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData(): array
    {

        $searchRequest = $this->getRequest();
        $searchResponse = $this->searchEngine->search($searchRequest);

        return $this->processResponse($searchResponse);
    }

    /**
     * Get request.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        $storeId      = $this->getStoreId() ?? $this->context->getStoreId();
        $aggregations = $this->getAggregations();
        $queryFilters = $this->getQueryFilters();

        return  $this->searchRequestBuilder->create(
            $storeId,
            $this->containerName,
            0,
            0,
            null,
            [],
            [],
            $queryFilters,
            $aggregations
        );
    }

    /**
     * Process response.
     *
     * @param \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponse $response response
     * @return array
     */
    protected function processResponse(
        \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponse $response
    ): array {
        $data = [];
        if ($response->getAggregations()->getBucket('campaigns')) {
            foreach ($response->getAggregations()->getBucket('campaigns')->getValues() as $campaign) {
                $campaignId  = $campaign->getValue();
                $data[$campaignId] = $this->getBaseCampaignSessionData();
                $data[$campaignId]['session_count'] = $campaign->getMetrics()['count'];

                $campaignScenarios = $campaign->getAggregations()->getBucket('campaign_scenarios');
                if ($campaignScenarios) {
                    foreach ($campaignScenarios->getValues() as $campaignScenario) {
                        $scenario  = $campaignScenario->getValue();
                        $sessionCount = $campaignScenario->getMetrics()['count'];
                        $data[$campaignId][$scenario]['session_count'] = $sessionCount;
                        $salesCount = $campaignScenario->getMetrics();
                        $salesCount = $salesCount['conversion']['sales']['buckets']['sales']['doc_count'] ?? 0;
                        $data[$campaignId][$scenario]['sales_count'] = $salesCount;
                        $data[$campaignId][$scenario]['conversion_rate'] = number_format(
                            $salesCount / $sessionCount,
                            2
                        );
                    }
                }
            }
        }

        foreach ($this->postProcessors as $postProcessor) {
            $data = $postProcessor->postProcessResponse($data);
        }

        return $data;
    }

    /**
     * Get base campaign session data.
     *
     * @return array
     */
    protected function getBaseCampaignSessionData(): array
    {
        $scenarioData = [
            'session_count' => 0,
            'sales_count' => 0,
            'conversion_rate' => 0,
        ];

        return [
            CampaignOptimizerInterface::SCENARIO_TYPE_A => $scenarioData,
            CampaignOptimizerInterface::SCENARIO_TYPE_B => $scenarioData,
        ];
    }

    /**
     * Get query filters.
     *
     * @return array
     */
    protected function getQueryFilters(): array
    {
        $queries = [];
        foreach ($this->queryProviders as $queryProvider) {
            $queries[] = $queryProvider->getQuery();
        }

        return array_filter($queries);
    }

    /**
     * Get aggregations.
     *
     * @return array
     */
    private function getAggregations(): array
    {
        return [
            'campaign_scenarios' => $this->aggregationFactory->create(
                BucketInterface::TYPE_TERM,
                [
                    'field' => 'ab_campaigns.id',
                    'name' => 'campaigns',
                    'nestedPath' => 'ab_campaigns',
                    'childBuckets' => [
                        $this->aggregationFactory->create(
                            BucketInterface::TYPE_TERM,
                            [
                                'field' => 'ab_campaigns.scenario',
                                'name' => 'campaign_scenarios',
                                'childBuckets' => [
                                    $this->aggregationFactory->create(
                                        BucketInterface::TYPE_REVERSE_NESTED,
                                        [
                                            'name' => 'conversion',
                                            'field' => null,
                                            'childBuckets' => [
                                                $this->aggregationFactory->create(
                                                    BucketInterface::TYPE_QUERY_GROUP,
                                                    [
                                                        'queries' => [
                                                            'sales' => $this->queryFactory->create(
                                                                QueryInterface::TYPE_EXISTS,
                                                                ['field' => 'product_sale']
                                                            ),
                                                        ],
                                                        'name' => 'sales',
                                                    ]
                                                ),
                                            ],
                                        ]
                                    ),
                                ],
                            ]
                        ),
                    ],
                ]
            ),
        ];
    }
}
