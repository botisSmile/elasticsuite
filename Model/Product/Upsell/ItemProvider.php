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

namespace Smile\ElasticsuiteRecommender\Model\Product\Upsell;

use Magento\Catalog\Api\Data\ProductInterface;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\AbstractItemProvider;
use Smile\ElasticsuiteRecommender\Model\Product\Matcher\CartProductProvider;
use Smile\ElasticsuiteRecommender\Helper\Data as DataHelper;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * Load a product collection with manual upsell products recommendations.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class ItemProvider extends AbstractItemProvider
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig       Catalog configuration.
     * @param CartProductProvider           $cartProductProvider Cart products provider.
     * @param DataHelper                    $helper              Helper.
     * @param EventManagerInterface         $eventManager        Event manager.
     */
    public function __construct(
        \Magento\Catalog\Model\Config $catalogConfig,
        CartProductProvider $cartProductProvider,
        DataHelper $helper,
        EventManagerInterface $eventManager
    ) {
        parent::__construct($catalogConfig, $cartProductProvider);
        $this->helper       = $helper;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(ProductInterface $product, $size)
    {
        $items = parent::getItems($product, $size);

        if ($this->helper->isNativeComplementAllowedForUpsells()) {
            $collectionMock = new \Magento\Framework\DataObject(['items' => $items]);
            $this->eventManager->dispatch(
                'catalog_product_upsell',
                [
                    'product'       => $product,
                    'collection'    => $collectionMock,
                    'limit'         => $size,
                ]
            );
            $items = $collectionMock->getItems();
            /*
             * The \Magento\Bundle\Observer\AppendUpsellProductsObserver observer does not correctly handle limit,
             * so the resulting items list might be longer than expected.
             */
            $items = array_slice($items, 0, $size, true);
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    protected function createCollection(ProductInterface $product)
    {
        return $product->getUpSellProductCollection();
    }
}
