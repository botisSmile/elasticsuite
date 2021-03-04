<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Collector;

use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Search\Position as PositionResource;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;
use Smile\ElasticsuiteExplain\Model\Result\Collector\Positions\ProviderInterface;

/**
 * Applied product positions collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Positions implements CollectorInterface
{
    /**
     * Collector type
     */
    const TYPE = 'positions';

    /**
     * @var ProviderInterface[]
     */
    private $positionsProviders;

    /**
     * Position constructor.
     *
     * @param ProviderInterface[] $positionsProviders Product positions providers.
     */
    public function __construct($positionsProviders = [])
    {
        $this->positionsProviders = $positionsProviders;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        $result      = [];
        $requestType = $containerConfiguration->getName();

        /** @var ProviderInterface $positionProvider */
        $positionProvider = $this->positionsProviders[$requestType] ?? null;

        if ($positionProvider) {
            $result = [self::TYPE => $positionProvider->getPositions($searchContext)];
        }

        return $result;
    }
}
