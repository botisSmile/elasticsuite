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
     * @var array
     */
    private $synonymCache = [];

    /**
     * Constructor.
     *
     * @param Index            $thesaurusIndex Theraurus Index.
     * @param ContextInterface $searchContext  Search Context.
     */
    public function __construct(
        Index $thesaurusIndex,
        ContextInterface  $searchContext
    ) {
        $this->thesaurusIndex = $thesaurusIndex;
        $this->searchContext  = $searchContext;
    }

    /**
     * Get synonym for a field query.
     *
     * @param string $fieldQuery Field query.
     *
     * @return string
     */
    public function getSynonym(string $fieldQuery): string
    {
        $queryText = $this->searchContext->getCurrentSearchQuery()->getQueryText();
        $storeId   = (int) $this->searchContext->getStoreId();

        // Retrieve all possible synonyms for the query text.
        $cacheKey = $storeId . '_' . $queryText;
        if (!isset($this->synonymCache[$cacheKey])) {
            $this->synonymCache[$cacheKey] = $this->thesaurusIndex->getSynonyms($storeId, $queryText);
        }

        // Find the synonym.
        $synonym = '';
        foreach ($this->synonymCache[$cacheKey] as $synonymData) {
            if ($fieldQuery === $synonymData['token']) {
                $synonym = substr(
                    $queryText,
                    $synonymData['start_offset'],
                    $synonymData['end_offset'] - $synonymData['start_offset']
                );
                break;
            }
        }

        return $synonym;
    }
}
