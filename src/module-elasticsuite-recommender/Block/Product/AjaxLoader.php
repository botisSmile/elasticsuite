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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteRecommender\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Ajax recommender loader block.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class AjaxLoader extends Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * URL helper
     *
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context   Template context.
     * @param \Magento\Framework\Registry                      $registry  Registry.
     * @param UrlHelper                                        $urlHelper URL helper.
     * @param array                                            $data      Data.
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        UrlHelper $urlHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Get encoded URL to redirect to (after compare)
     *
     * @return string
     */
    public function getRedirectEncodedUrl()
    {
        return $this->urlHelper->getEncodedUrl();
    }

    /**
     * Get URL for ajax call
     *
     * @return string
     */
    public function getProductRecommenderUrl()
    {
        return $this->getUrl(
            'elasticsuite/recommender/ajax',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id' => $this->getProductId(),
            ]
        );
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    private function getProductId()
    {
        $product = $this->coreRegistry->registry('product');

        return $product ? $product->getId() : null;
    }
}
