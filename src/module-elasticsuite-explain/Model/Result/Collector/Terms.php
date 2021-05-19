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

namespace Smile\ElasticsuiteExplain\Model\Result\Collector;

use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider as TermDataProvider;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;

/**
 * Terms collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Terms implements CollectorInterface
{
    /**
     * Collector type
     */
    const TYPE = 'terms';

    /**
     * @var TermDataProvider
     */
    private $termDataProvider;

    /**
     * Terms constructor.
     *
     * @param TermDataProvider $termDataProvider Term data provider
     */
    public function __construct(
        TermDataProvider $termDataProvider
    ) {
        $this->termDataProvider = $termDataProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        if ($containerConfiguration->getName() !== 'catalog_product_autocomplete') {
            return [];
        }

        return [self::TYPE => $this->getTerms()];
    }

    /**
     * Get popular terms from term data provider.
     *
     * @return array
     */
    private function getTerms()
    {
        $terms = $this->termDataProvider->getItems();
        $result = [];
        foreach ($terms as $term) {
            $result[] = [
                'title'       => $term->getTitle(),
                'type'        => $term->getData('type'),
                'num_results' => $term->getData('num_results'),
            ];
        }

        return $result;
    }
}
