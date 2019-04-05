<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\CollectionFactory;
use Magento\Catalog\Model\Config as CatalogConfig;

/**
 * Init a collection for recommender model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class CollectionProvider
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * Constructor.
     *
     * @param CollectionFactory $productCollectionFactory Product collection factory
     * @param CatalogConfig     $catalogConfig            Catalog config.
     */
    public function __construct(CollectionFactory $productCollectionFactory, CatalogConfig $catalogConfig)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogConfig            = $catalogConfig;
    }

    /**
     * Create an prepare the collection.
     *
     * @return \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection
     */
    public function getCollection()
    {
        $collection = $this->productCollectionFactory->create();

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addUrlRewrite();

        return $collection;
    }
}
