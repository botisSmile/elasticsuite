<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteRecommender\Model;

use Smile\ElasticsuiteCore\Search\Request\Builder as SearchRequestBuilder;
use Magento\Framework\Search\SearchEngineInterface;
use Smile\ElasticsuiteTracker\Api\SessionIndexInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use function GuzzleHttp\json_encode;
use Smile\ElasticsuiteRecommender\Helper\Data as RecommenderHelper;

/**
 * Find co-occurrences across event into the session data.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class CoOccurrence
{
    /**
     * @var SearchRequestBuilder
     */
    private $searchRequestBuilder;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var RecommenderHelper
     */
    private $helper;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Constructor.
     *
     * @param SearchRequestBuilder  $searchRequestBuilder Search request builder.
     * @param SearchEngineInterface $searchEngine         Search engine.
     * @param RecommenderHelper     $helper               Recommender helper.
     */
    public function __construct(
        SearchRequestBuilder $searchRequestBuilder,
        SearchEngineInterface $searchEngine,
        RecommenderHelper $helper
    ) {
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->searchEngine = $searchEngine;
        $this->helper = $helper;
    }

    /**
     * Match observed co-occurrences into the session data.
     *
     * @param string  $sourceEventType  Source event type (product_view, category_view, ...)
     * @param mixed   $sourceEventValue Event value.
     * @param integer $storeId          Store id.
     * @param string  $targetEventType  Target event to match co-occurrences.
     * @param integer $size             Number of co-occurrences to find.
     *
     * @return string[]
     */
    public function getCoOccurrences($sourceEventType, $sourceEventValue, $storeId, $targetEventType, $size = 5)
    {
        $cacheKey = md5(json_encode(func_get_args()));

        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->loadCoOccurrences($sourceEventType, $sourceEventValue, $storeId, $targetEventType, $size);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * Load the co-occurrences.
     *
     * @param string  $sourceEventType  Source event type (product_view, category_view, ...)
     * @param mixed   $sourceEventValue Event value.
     * @param integer $storeId          Store id.
     * @param string  $targetEventType  Target event to match co-occurrences.
     * @param integer $size             Number of co-occurrences to find.
     *
     * @return string[]
     */
    private function loadCoOccurrences($sourceEventType, $sourceEventValue, $storeId, $targetEventType, $size = 5)
    {
        $values = [];

        try {
            $sessionFilter = $this->getSessionFilter($sourceEventType, $sourceEventValue);
            $aggregations  = $this->getAggregations($targetEventType, $size);

            $searchRequest  = $this->getSearchRequest($storeId, $sessionFilter, $aggregations);
            $searchResponse = $this->searchEngine->search($searchRequest);

            foreach ($searchResponse->getAggregations()->getBucket($targetEventType)->getValues() as $value) {
                if ($value->getValue() != "__other_docs") {
                    $values[] = $value->getValue();
                }
            }
        } catch (\Exception $e) {
            ;
        }

        return $values;
    }

    /**
     * Build the search request used to match co-occurrences.
     *
     * @param integer $storeId       Store id.
     * @param array   $sessionFilter Session filter.
     * @param string  $aggregations  Aggregations.
     *
     * @return \Smile\ElasticsuiteCore\Search\RequestInterface
     */
    private function getSearchRequest($storeId, $sessionFilter, $aggregations)
    {
        $index = SessionIndexInterface::INDEX_IDENTIFIER;

        return $this->searchRequestBuilder->create($storeId, $index, 0, 0, null, [], [], $sessionFilter, $aggregations);
    }

    /**
     * Filter used to match sessions.
     *
     * @param string $sourceEventType  Source event type (product_view, category_view, ...)
     * @param mixed  $sourceEventValue Event value.
     *
     * @return array
     */
    private function getSessionFilter($sourceEventType, $sourceEventValue)
    {
        return [$sourceEventType => $sourceEventValue];
    }

    /**
     * Build the aggregation used to match significant coocurences.
     *
     * @param string  $targetEventType Target event type.
     * @param integer $size            Aggregation type.
     *
     * @return array
     */
    private function getAggregations($targetEventType, $size)
    {
        $config = [
            'type'        => BucketInterface::TYPE_SIGNIFICANT_TERM,
            'name'        => $targetEventType,
            'field'       => $targetEventType,
            'size'        => $size,
            'minDocCount' => $this->helper->getCoOccurrenceMinDocCount(),
        ];

        return [
            $targetEventType => $config,
        ];
    }
}
