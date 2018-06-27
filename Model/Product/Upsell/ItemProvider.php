<?php

namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\AbstractItemProvider;

class ItemProvider extends AbstractItemProvider
{
    protected function createCollection(ProductInterface $product)
    {
        return $product->getUpSellProductCollection();
    }
}
