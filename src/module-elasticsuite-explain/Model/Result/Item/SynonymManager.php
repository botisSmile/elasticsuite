<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Item;

use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Model\Autocomplete\QueryProvider;
use Smile\ElasticsuiteExplain\Model\Thesaurus\Index;

/**
 * Synonym Manager.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pilem@smile.fr>
 */
class SynonymManager
{
    /**
     * @var Index
     */
    private $thesaurusIndex;

    /**
     * @var ContextInterface
     */
    private $searchContext;

    /**
     * @var QueryProvider
     */
    private $autocompleteQueryProvider;

    /**
     * @var array
     */
    private $synonymCache = [];

    /**
     * Constructor.
     *
     * @param Index            $thesaurusIndex            Theraurus Index.
     * @param ContextInterface $searchContext             Search Context.
     * @param QueryProvider    $autocompleteQueryProvider Autocomplete query provider
     */
    public function __construct(
        Index $thesaurusIndex,
        ContextInterface  $searchContext,
        QueryProvider $autocompleteQueryProvider
    ) {
        $this->thesaurusIndex = $thesaurusIndex;
        $this->searchContext  = $searchContext;
        $this->autocompleteQueryProvider = $autocompleteQueryProvider;
    }

    /**
     * Get synonym for a field query.
     *
     * @param string $fieldQuery Field query.
     * @return string
     */
    public function getSynonym(string $fieldQuery): string
    {
        $queryTexts = $this->getQueryTexts();
        $storeId   = (int) $this->searchContext->getStoreId();
        $synonym = '';
        foreach ($queryTexts as $queryText) {
            // Retrieve all possible synonyms for the query text.
            $cacheKey = $storeId . '_' . $queryText;
            if (!isset($this->synonymCache[$cacheKey])) {
                $this->synonymCache[$cacheKey] = $this->thesaurusIndex->getSynonyms($storeId, $queryText);
            }

            // Find the synonym.
            foreach ($this->synonymCache[$cacheKey] as $synonymData) {
                if ($fieldQuery === $synonymData['token']) {
                    $synonym = substr(
                        $queryText,
                        $synonymData['start_offset'],
                        $synonymData['end_offset'] - $synonymData['start_offset']
                    );
                    break 2;
                }
            }
        }

        return $synonym;
    }

    /**
     * Get query texts.
     *
     * @return array
     */
    private function getQueryTexts()
    {
        $currentQueryText = $this->searchContext->getCurrentSearchQuery()->getQueryText();

        return $this->autocompleteQueryProvider->getQueryTextForAutocomplete() ?: [$currentQueryText];
    }
}
