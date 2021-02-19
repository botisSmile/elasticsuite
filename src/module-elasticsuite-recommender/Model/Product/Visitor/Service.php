<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Visitor;

use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider\ContextInterfaceFactory as ProductProviderContextInterfaceFactory;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterfaceFactory;

/**
 * Visitor recommendations service.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Service
{
    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterface
     */
    private $productProvider;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProviderInterfaceFactory
     */
    private $productProviderFactory;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider\ContextInterfaceFactory
     */
    private $providerContextFactory;

    /**
     * @var \Smile\ElasticsuiteRecommender\Model\Product\Matcher\Multiple
     */
    private $model;

     /**
     * Constructor.
     *
     * @param ProductProviderInterfaceFactory                               $productProviderFactory Product provider factory.
     * @param ProductProviderContextInterfaceFactory                        $providerContextFactory Product provider context.
     * @param \Smile\ElasticsuiteRecommender\Model\Product\Matcher\Multiple $model                  Recommender model.
     */
    public function __construct(
        ProductProviderInterfaceFactory $productProviderFactory,
        ProductProviderContextInterfaceFactory $providerContextFactory,
        \Smile\ElasticsuiteRecommender\Model\Product\Matcher\Multiple $model
    ) {
        $this->productProviderFactory = $productProviderFactory;
        $this->providerContextFactory = $providerContextFactory;
        $this->model                  = $model;
    }

    /**
     * Get a list of product ids recommended for the current visitor.
     *
     * @param int|null $maxAge     Max age of events to compute recommendations on
     * @param int|null $maxSize    Max number of product ids to fetch
     * @param array    $categories Categories to restrain the recommendations, if any.
     *
     * @return array
     */
    public function getRecommendedProductIds($maxAge = null, $maxSize = null, $categories = [])
    {
        $productIds = [];

        $context    = $this->providerContextFactory->create(
            ['maxAge' => $maxAge, 'maxSize' => $maxSize, 'categories' => $categories]
        );

        $this->productProvider = $this->productProviderFactory->create(['context' => $context]);
        $sourceProducts        = $this->productProvider->getProducts();

        if (!empty($sourceProducts)) {
            $items = $this->model->getItems($sourceProducts, $maxSize);
            foreach ($items as $product) {
                $productIds[] = $product->getId();
            }
        }

        return $productIds;
    }
}
