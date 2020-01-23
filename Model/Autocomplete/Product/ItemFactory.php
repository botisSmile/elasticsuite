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
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager Object manager.
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($objectManager);
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

        return $source;
    }
}
