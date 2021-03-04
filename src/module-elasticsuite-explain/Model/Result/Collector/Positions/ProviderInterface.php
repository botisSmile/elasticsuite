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

use Smile\ElasticsuiteCore\Api\Search\ContextInterface;

/**
 * Product positions provider interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface ProviderInterface
{
    /**
     * Get product positions for the current query, if available.
     *
     * @param ContextInterface $searchContext Search Context
     *
     * @return array
     */
    public function getPositions(ContextInterface $searchContext);
}
