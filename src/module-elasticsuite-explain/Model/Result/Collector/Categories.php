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

use Smile\ElasticsuiteCatalog\Model\Autocomplete\Category\DataProvider;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteExplain\Model\Result\CollectorInterface;

/**
 * Categories collector.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Categories implements CollectorInterface
{
    /**
     * Collector type
     */
    const TYPE = 'categories';

    /**
     * @var DataProvider
     */
    private $categoryDataProvider;

    /**
     * Categories constructor.
     *
     * @param DataProvider $categoryDataProvider Category data provider
     */
    public function __construct(
        DataProvider $categoryDataProvider
    ) {
        $this->categoryDataProvider = $categoryDataProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(ContextInterface $searchContext, ContainerConfigurationInterface $containerConfiguration)
    {
        if ($containerConfiguration->getName() !== 'catalog_product_autocomplete') {
            return [];
        }

        return [self::TYPE => $this->getCategories()];
    }

    /**
     * Get applicable optimizers.
     *
     * @return array
     */
    private function getCategories()
    {
        $categories = $this->categoryDataProvider->getItems();
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'title'      => $category->getTitle(),
                'type'       => $category->getData('type'),
                'url'        => $category->getData('url'),
                'breadcrumb' => $category->getData('breadcrumb') ?: [],
            ];
        }

        return $result;
    }
}
