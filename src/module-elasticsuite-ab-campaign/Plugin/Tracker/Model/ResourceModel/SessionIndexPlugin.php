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

namespace Smile\ElasticsuiteAbCampaign\Plugin\Tracker\Model\ResourceModel;

use Magento\Framework\Search\SearchEngineInterface;
use Smile\ElasticsuiteCore\Search\Request\Builder;
use Smile\ElasticsuiteCore\Search\RequestInterface;
use Smile\ElasticsuiteTracker\Model\ResourceModel\SessionIndex;

/**
 * Session index plugin.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class SessionIndexPlugin
{
    /**
     * @var string
     */
    protected const SEARCH_REQUEST_CONTAINER = 'session_aggregator_ab_campaign';

    /**
     * @var Builder
     */
    protected $searchRequestBuilder;

    /**
     * @var SearchEngineInterface
     */
    protected $searchEngine;

    /**
     * Constructor.
     *
     * @param Builder               $searchRequestBuilder Search request builder.
     * @param SearchEngineInterface $searchEngine         Search engine.
     */
    public function __construct(
        Builder $searchRequestBuilder,
        SearchEngineInterface $searchEngine
    ) {
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->searchEngine         = $searchEngine;
    }

    /**
     * Add campaign data  in session index data.
     *
     * @param SessionIndex $sessionIndex Session index.
     * @param array        $data         Data.
     * @param int          $storeId      Store id.
     * @param string[]     $sessionIds   Session ids.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSessionData(SessionIndex $sessionIndex, array $data, int $storeId, array $sessionIds): array
    {
        $data = array_combine(array_column($data, 'session_id'), $data);
        $searchRequest  = $this->getSearchRequest($storeId, $sessionIds);
        $searchResponse = $this->searchEngine->search($searchRequest);

        if ($searchResponse->getAggregations()->getBucket('session_id') !== null) {
            foreach ($searchResponse->getAggregations()->getBucket('session_id')->getValues() as $sessionValue) {
                $sessionId = $sessionValue->getValue();
                if (!empty($sessionValue->getAggregations()->getBucket('campaigns'))) {
                    foreach ($sessionValue->getAggregations()->getBucket('campaigns')->getValues() as $campaign) {
                        $campaignId = $campaign->getValue();
                        $campaignScenario = $campaign->getAggregations()->getBucket('campaign_scenario');
                        if (!empty($campaignScenario)) {
                            foreach ($campaignScenario->getValues() as $scenario) {
                                $data[$sessionId]['ab_campaigns'][] = [
                                    'id' => $campaignId,
                                    'scenario' => $scenario->getValue(),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Build search request used to collect aggregated session data.
     *
     * @param int      $storeId    Store Id.
     * @param string[] $sessionIds Session ids.
     *
     * @return RequestInterface
     */
    private function getSearchRequest(int $storeId, array $sessionIds): RequestInterface
    {
        $queryFilters = ['session.uid' => $sessionIds];

        return $this->searchRequestBuilder->create(
            $storeId,
            self::SEARCH_REQUEST_CONTAINER,
            0,
            0,
            null,
            [],
            [],
            $queryFilters
        );
    }
}
