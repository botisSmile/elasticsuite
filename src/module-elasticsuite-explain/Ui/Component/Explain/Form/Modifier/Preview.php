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

namespace Smile\ElasticsuiteExplain\Ui\Component\Explain\Form\Modifier;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Modifier for adminhtml explain form
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Preview implements ModifierInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * Preview constructor.
     *
     * @param \Magento\Backend\Model\UrlInterface       $urlBuilder   Url Builder
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat Locale Format
     */
    public function __construct(UrlInterface $urlBuilder, FormatInterface $localeFormat)
    {
        $this->urlBuilder   = $urlBuilder;
        $this->localeFormat = $localeFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $config = [
            'loadUrl'      => $this->getPreviewUrl(),
            'price_format' => $this->localeFormat->getPriceFormat(),
        ];

        $meta['general']['children']['explain_preview']['arguments']['data']['config'] = $config;

        return $meta;
    }

    /**
     * Retrieve the explain results URL.
     *
     * @return string
     */
    private function getPreviewUrl()
    {
        $urlParams = ['ajax' => true];

        return $this->urlBuilder->getUrl('smile_elasticsuite_explain/explain/results', $urlParams);
    }
}
