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

use Magento\Search\Model\Autocomplete\Item as TermItem;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider as TermDataProvider;
use Smile\ElasticsuiteExplain\Model\Autocomplete\QueryProvider;
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
     * @var Index
     */
    private $index;

    /**
     * @var QueryProvider
     */
    private $autocompleteQueryProvider;

    /**
     * Synonyms constructor.
     *
     * @param Index         $index                     Thesaurus Index
     * @param QueryProvider $autocompleteQueryProvider Autocomplete query provider
     */
    public function __construct(Index $index, QueryProvider $autocompleteQueryProvider)
    {
        $this->index = $index;
        $this->autocompleteQueryProvider = $autocompleteQueryProvider;
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
            $rewrites = [];
            foreach ($this->getQueryTexts($searchContext, $containerConfiguration) as $item) {
                $rewrites = array_merge($rewrites, $this->index->getQueryRewrites($containerConfiguration, $item));
            }
        }

        return array_keys($rewrites);
    }

    /**
     * Get query texts.
     *
     * @param ContextInterface                $searchContext          Search context
     * @param ContainerConfigurationInterface $containerConfiguration Container configuration
     * @return array
     */
    private function getQueryTexts(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        $queryTexts = [$searchContext->getCurrentSearchQuery()->getQueryText()];
        if ($containerConfiguration->getName() === 'catalog_product_autocomplete') {
            $queryTexts = $this->autocompleteQueryProvider->getQueryTextForAutocomplete() ?: $queryTexts;
        }

        return $queryTexts;
    }
}
