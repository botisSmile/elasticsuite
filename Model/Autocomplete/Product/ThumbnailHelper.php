<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Helper\ImageFactory as ImageHelperFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreManagerInterfaceFactory;

/**
 * Thumbnail helper for instant search.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ThumbnailHelper
{
    /**
     * @var ImageHelperFactory
     */
    private $imageHelperFactory;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    private $productRepositoryFactory;

    /**
     * @var StoreManagerInterfaceFactory
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param ImageHelperFactory $imageHelperFactory Catalog product image helper.
     */
    public function __construct(
        ImageHelperFactory $imageHelperFactory,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        StoreManagerInterfaceFactory $storeManagerFactory
    ) {
        $this->imageHelperFactory       = $imageHelperFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->storeManagerFactory      = $storeManagerFactory;
    }

    /**
     * Get resized image URL.
     *
     * @param int   $productId Product Id
     * @param string $imageId  The image name
     *
     * @return string
     */
    public function getImageUrl($productId, $imageId = ItemFactory::AUTOCOMPLETE_IMAGE_ID)
    {
        try {
            $product = $this->getProductRepository()->getById($productId, false, $this->getStoreManager()->getStore()->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return '';
        }

        $helper = $this->getImageHelper();
        $helper->init($product, $imageId);

        return $helper->getUrl();
    }

    /**
     * @return ProductRepositoryInterface
     */
    private function getProductRepository()
    {
        return $this->productRepositoryFactory->create();
    }

    /**
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        return $this->storeManagerFactory->create();
    }

    /**
     * @return ImageHelper
     */
    private function getImageHelper()
    {
        return $this->imageHelperFactory->create();
    }

}
