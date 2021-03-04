<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Collector\Positions;

use Smile\ElasticsuiteVirtualCategory\Model\ResourceModel\Category\Product\Position as PositionResource;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;

/**
 * Applied category position collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Category implements ProviderInterface
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
    public function getPositions(ContextInterface $searchContext)
    {
        $positions = [];

        if ($searchContext->getCurrentCategory() && $searchContext->getCurrentCategory()->getId()) {
            $positions = $this->positionResource->getProductPositionsByCategory($searchContext->getCurrentCategory());
        }

        return $positions;
    }
}
