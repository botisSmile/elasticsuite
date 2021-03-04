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

use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;
use Smile\ElasticsuiteThesaurus\Model\Index;

/**
 * Synonyms collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Synonyms implements CollectorInterface
{
    /**
     * Collector type
     */
    const TYPE = 'synonyms';

    /**
     * @var \Smile\ElasticsuiteThesaurus\Model\Index
     */
    private $index;

    /**
     * Synonyms constructor.
     *
     * @param \Smile\ElasticsuiteThesaurus\Model\Index $index Thesaurus Index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        return [self::TYPE => $this->getSynonyms($searchContext, $containerConfiguration)];
    }

    /**
     * Get applicable synonyms.
     *
     * @param ContextInterface                $searchContext          Search Context
     * @param ContainerConfigurationInterface $containerConfiguration Container configuration
     *
     * @return array
     */
    private function getSynonyms(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        $rewrites = [];

        if ($searchContext->getCurrentSearchQuery()) {
            $rewrites = $this->index->getQueryRewrites($containerConfiguration, $searchContext->getCurrentSearchQuery()->getQueryText());
        }

        return array_keys($rewrites);
    }
}
