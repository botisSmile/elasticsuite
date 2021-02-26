<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteExplain\Model\Result;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Customer\Api\Data\GroupInterface;
use Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response\ExplainDocument;

/**
 * Result Item Model for Explain
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Item
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    private $document;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * Constructor.
     *
     * @param ProductInterface $product     Product
     * @param ExplainDocument  $document    Product Document.
     * @param ImageHelper      $imageHelper Image helper.
     */
    public function __construct(
        ProductInterface $product,
        ExplainDocument $document,
        ImageHelper $imageHelper
    ) {
        $this->product       = $product;
        $this->document      = $document;
        $this->imageHelper   = $imageHelper;
    }

    /**
     * Item data.
     *
     * @return array
     */
    public function getData()
    {
        $data = [
            'id'          => (int) $this->document->getId(),
            'name'        => $this->getDocumentSource('name'),
            'sku'         => $this->getDocumentSource('sku'),
            'price'       => $this->getProductPrice(),
            'image'       => $this->getProductImage(),
            'score'       => $this->getDocumentScore(),
            'explanation' => $this->getDocumentExplanation(),
            'sort'        => $this->getDocumentSort(),
            'is_in_stock' => $this->isInStockProduct(),
        ];

        return $data;
    }

    /**
     * Return the ES explanation document for the current product.
     *
     * @param string $field The document field to retrieve.
     *
     * @return array
     */
    private function getDocumentExplanation($field = null)
    {
        return $this->document->getExplain() ? : [];
    }

    /**
     * Return the ES source document for the current product.
     *
     * @param string $field The document field to retrieve.
     *
     * @return array
     */
    private function getDocumentSource($field = null)
    {
        $docSource = $this->document->getSource() ? : [];
        $result    = $docSource;

        if (null !== $field) {
            $result = null;
            if (isset($docSource[$field])) {
                $result = is_array($docSource[$field]) ? current($docSource[$field]) : $docSource[$field];
            }
        }

        return $result;
    }

    /**
     * Return the ES score for the current product.
     *
     * @return array
     */
    private function getDocumentScore()
    {
        return $this->document->getScore();
    }

    /**
     * Return the ES sort values for the current product.
     *
     * @return array
     */
    private function getDocumentSort()
    {
        return $this->document->getSort() ?: [];
    }

    /**
     * Retrieve product small image
     *
     * @return string
     */
    private function getProductImage()
    {
        $image = $this->getDocumentSource('image');
        if ($image) {
            $this->product->setSmallImage($image);
        }

        $this->imageHelper->init($this->product, 'smile_elasticsuite_product_sorter_image');

        return $this->imageHelper->getUrl();
    }

    /**
     * Returns current product sale price.
     *
     * @return float
     */
    private function getProductPrice()
    {
        $price    = 0;
        $document = $this->getDocumentSource();

        if (isset($document['price'])) {
            foreach ($document['price'] as $currentPrice) {
                if ((int) $currentPrice['customer_group_id'] === GroupInterface::NOT_LOGGED_IN_ID) {
                    $price = (float) $currentPrice['price'];
                }
            }
        }

        return $price;
    }

    /**
     * Returns current product stock status.
     *
     * @return bool
     */
    private function isInStockProduct()
    {
        $isInStock = false;
        $document = $this->getDocumentSource();
        if (isset($document['stock']['is_in_stock'])) {
            $isInStock = (bool) $document['stock']['is_in_stock'];
        }

        return $isInStock;
    }
}
