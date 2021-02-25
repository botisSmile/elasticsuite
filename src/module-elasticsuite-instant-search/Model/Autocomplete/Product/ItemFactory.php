<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\Render;

/**
 * Create an autocomplete item from a product.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class ItemFactory extends \Magento\Search\Model\Autocomplete\ItemFactory
{
    /**
     * Autocomplete image id (used for resize)
     */
    const AUTOCOMPLETE_IMAGE_ID = 'smile_elasticsuite_autocomplete_product_image';

    /**
     * XML path for product url suffix
     */
    const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';

    /**
     * @var null
     */
    private $productUrlSuffix = null;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager Object manager.
     * @param ScopeConfigInterface   $scopeConfig   The Scope Config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($objectManager);
        $this->productUrlSuffix = $scopeConfig->getValue(self::XML_PATH_PRODUCT_URL_SUFFIX);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data)
    {
        $data = $this->addProductData($data);
        unset($data['source']);

        return parent::create($data);
    }

    /**
     * Load product data and append them to the original data.
     *
     * @param array $data Autocomplete item data.
     *
     * @return array
     */
    private function addProductData($data)
    {
        $source              = $data['source'];
        $source['type']      = $data['type'];
        $source['thumbnail'] = 'instantsearch/ajax/thumbnail?productId=' . $source['entity_id'];
        $source['url']       = $this->getProductUrl($source);
        $source['highlightCategory'] = $this->getHighlightCategory($source);

        return $source;
    }

    /**
     * Retrieve product Url from the document source.
     * Done from the document source to prevent having to rely on DB.
     *
     * @param array $documentSource Document Source.
     *
     * @return string
     */
    private function getProductUrl($documentSource)
    {
        if ($documentSource && isset($documentSource['url_key'])) {
            $urlPath = is_array($documentSource['url_key']) ? current($documentSource['url_key']) : $documentSource['url_key'];

            $url = trim($urlPath, '/') . $this->productUrlSuffix;

            return $url;
        }

        return '';
    }

    /**
     * Retrieve category to highlight from the document source.
     * Done from the document source to prevent having to rely on DB.
     *
     * @param array $documentSource Document Source.
     *
     * @return string
     */
    private function getHighlightCategory($documentSource)
    {
        if ($documentSource && isset($documentSource['category'])) {
            if (is_array($documentSource['category'])) {
                foreach ($documentSource['category'] as $category) {
                    $categoryName = $category['name'] ?? '';
                    if ($categoryName !== '') {
                        return $categoryName;
                    }
                }
            }
        }

        return '';
    }
}
