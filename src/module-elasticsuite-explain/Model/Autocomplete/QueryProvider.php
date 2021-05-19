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

namespace Smile\ElasticsuiteExplain\Model\Autocomplete;

use Magento\Search\Model\Autocomplete\Item as TermItem;
use Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider as TermDataProvider;

/**
 * Query provider for autocompletion.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class QueryProvider
{
    /**
     * @var TermDataProvider
     */
    private $termDataProvider;

    /**
     * Constructor.
     *
     * @param TermDataProvider $termDataProvider Term data provider
     */
    public function __construct(
        TermDataProvider $termDataProvider
    ) {
        $this->termDataProvider = $termDataProvider;
    }

    /**
     * List of search terms suggested by the search terms data provider.
     *
     * @return array
     */
    public function getQueryTextForAutocomplete()
    {
        return array_map(
            function (TermItem $termItem) {
                return $termItem->getTitle();
            },
            $this->termDataProvider->getItems()
        );
    }
}
