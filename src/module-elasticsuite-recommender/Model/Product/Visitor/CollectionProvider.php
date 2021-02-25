<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Visitor;

use Magento\Catalog\Model\Config as CatalogConfig;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory;
use Smile\ElasticsuiteCore\Api\Search\ContextInterface;

/**
 * Visitor Recommendations Collection Provider.
 * Extended to apply arbitrary filter to the currently viewed category, if any.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class CollectionProvider extends \Smile\ElasticsuiteRecommender\Model\Product\Matcher\CollectionProvider
{
    /**
     * @var ContextInterface
     */
    private $searchContext;

    /**
     * Constructor.
     *
     * @param CollectionFactory $productCollectionFactory Product collection factory
     * @param CatalogConfig     $catalogConfig            Catalog config.
     * @param ContextInterface  $context                  Search Context
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        CatalogConfig $catalogConfig,
        ContextInterface $context
    ) {
        parent::__construct($productCollectionFactory, $catalogConfig);
        $this->searchContext = $context;
    }

    /**
     * Create an prepare the collection.
     *
     * @return \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection
     */
    public function getCollection()
    {
        $collection = parent::getCollection();

        if ($this->searchContext->getCurrentCategory() && $this->searchContext->getCurrentCategory()->getId()) {
            $collection->addCategoryFilter($this->searchContext->getCurrentCategory());
        }

        return $collection;
    }
}
