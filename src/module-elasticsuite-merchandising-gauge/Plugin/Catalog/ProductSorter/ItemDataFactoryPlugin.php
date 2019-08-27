<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Plugin\Catalog\ProductSorter;

use Smile\ElasticsuiteCatalog\Model\ProductSorter\ItemDataFactory;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Product sorter item model plugin.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class ItemDataFactoryPlugin
{
    /**
     * After plugin - adds behavioral statistics about the product to the item data.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ItemDataFactory  $subject         Item data factory.
     * @param array            $productItemData Product item data.
     * @param ProductInterface $product         Product to get data from.
     *
     * @return array
     */
    public function afterGetData(
        ItemDataFactory $subject,
        $productItemData,
        ProductInterface $product
    ) {
        $productItemData['stats'] = $this->getBehavioralStats($product);

        return $productItemData;
    }

    /**
     * Get behavioral stats for a given product.
     * TODO ribay@smile.fr : post-processing
     *
     * @param ProductInterface $product Product.
     *
     * @return array
     */
    private function getBehavioralStats(ProductInterface $product)
    {
        $stats = [];

        $document = $this->getDocumentSource($product);
        if (isset($document['_stats'])) {
            $stats = $document['_stats'];
        }

        return $stats;
    }

    /**
     * Return the ES source document for the current product.
     *
     * @param ProductInterface $product Product.
     *
     * @return array
     */
    private function getDocumentSource(ProductInterface $product)
    {
        return $product->getDocumentSource() ? : [];
    }
}
