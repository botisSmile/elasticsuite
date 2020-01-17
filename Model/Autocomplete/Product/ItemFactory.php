<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product;

use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\Render;

/**
 * Create an autocomplete item from a product.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalog
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class ItemFactory extends \Magento\Search\Model\Autocomplete\ItemFactory
{
    /**
     * Autocomplete image id (used for resize)
     */
    const AUTOCOMPLETE_IMAGE_ID = 'smile_elasticsuite_autocomplete_product_image';

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * Constructor.
     *
     * @param ImageHelper $imageHelper Catalog product image helper.
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ImageHelper $imageHelper
    ) {
        parent::__construct($objectManager);
        $this->imageHelper = $imageHelper;
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
        $source = $data['source'];

        return $source;
    }

    /**
     * Get resized image URL.
     *
     * @param ProductInterface $product Current product.
     *
     * @return string
     */
    private function getImageUrl($product)
    {
        $this->imageHelper->init($product, self::AUTOCOMPLETE_IMAGE_ID);

        return $this->imageHelper->getUrl();
    }
}
