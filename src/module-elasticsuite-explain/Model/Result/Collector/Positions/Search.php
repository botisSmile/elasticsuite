<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteExplain
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Collector\Positions;

use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Search\Position as PositionResource;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;

/**
 * Applied search position collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class Search implements ProviderInterface
{
    /**
     * @var PositionResource
     */
    private $positionResource;

    /**
     * Position constructor.
     *
     * @param PositionResource $positionResource Search position resource model.
     */
    public function __construct(PositionResource $positionResource)
    {
        $this->positionResource = $positionResource;
    }

    /**
     * Get search positions for the current query, if available.
     *
     * @param ContextInterface $searchContext Search Context
     *
     * @return array
     */
    public function getPositions($searchContext)
    {
        $positions = [];

        if ($searchContext->getCurrentSearchQuery()) {
            $positions = $this->positionResource->getProductPositionsByQuery($searchContext->getCurrentSearchQuery());
        }

        return $positions;
    }
}