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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
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
            'boosts'      => $this->getBoosts(),
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

    /**
     * Return boosts applied to the product.
     *
     * @return array
     */
    private function getBoosts()
    {
        $boosts = [];

        if ($explain = $this->getDocumentExplanation()) {
            $boosts = $this->getBoostsFromExplain($explain);
        }

        return $boosts;
    }

    /**
     * Get boost weights from explain.
     *
     * @param array $explain Explain data
     *
     * @return array
     */
    private function getBoostsFromExplain(array $explain)
    {
        $boosts = [];

        if (array_key_exists('description', $explain)) {
            $description = $explain['description'];

            // On a function score node, extract boost/weight.
            if (preg_match('/^function score, score mode/', $description)) {
                if (array_key_exists('value', $explain)) {
                    $boosts['weight'] = $explain['value'];
                }
                if (array_key_exists('details', $explain) && is_array($explain['details'])) {
                    $boosts['total'] = count($explain['details'] ?? []);
                    if (!empty($explain['details'])) {
                        $boosts['details'] = $explain['details'];
                    }
                }
                if (preg_match('/\\[(.*?)\\]/', $description, $operator)) {
                    $boosts['operator'] = next($operator);
                }
            } elseif (array_key_exists('details', $explain)) {
                $details = $explain['details'];
                if (is_array($details)) {
                    foreach ($details as $child) {
                        $boosts = array_replace_recursive($boosts, $this->getBoostsFromExplain($child));
                    }
                }
            }
        }

        return $boosts;
    }
}
