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

namespace Smile\ElasticsuiteExplain\Model\Result;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Customer\Api\Data\GroupInterface;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response\ExplainDocument;

/**
 * Explain Collector interface.
 *
 * Should be implemented to collect particular data : applied optimizers, applied synonyms, etc...
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface CollectorInterface
{
    /**
     * Collect data from the search context and container configuration.
     * Will return it on a key value pair.
     * Eg :
     *
     * ['applied_optimizers' => [a, b, c]]
     *
     * @param \Smile\ElasticsuiteCore\Api\Search\ContextInterface                        $searchContext          Search Context
     * @param \Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface $containerConfiguration Container configuration
     *
     * @return array
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration);
}
