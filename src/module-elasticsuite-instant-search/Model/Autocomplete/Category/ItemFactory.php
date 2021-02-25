<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Category;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Create an autocomplete item from a category.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalog
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ItemFactory extends \Magento\Search\Model\Autocomplete\ItemFactory
{
    /**
     * XML path for category url suffix
     */
    const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    /**
     * @var null
     */
    private $categoryUrlSuffix = null;

    /**
     * ItemFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager The Object Manager
     * @param ScopeConfigInterface   $scopeConfig   The Scope Config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($objectManager);
        $this->categoryUrlSuffix = $scopeConfig->getValue(self::XML_PATH_CATEGORY_URL_SUFFIX);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data)
    {
        $data = $this->addCategoryData($data);
        unset($data['source']);

        return parent::create($data);
    }

    /**
     * Load category data and append them to the original data.
     *
     * @param array $data Autocomplete item data.
     *
     * @return array
     */
    private function addCategoryData($data)
    {
        $source         = $data['source'];
        $source['type'] = $data['type'];

        $title = $source['name'];
        if (is_array($title)) {
            $title = current($title);
        }

        $categoryData = [
            'title' => html_entity_decode($title),
            'url'   => $this->getCategoryUrl($source),
        ];

        $data = array_merge($source, $categoryData);

        return $data;
    }

    /**
     * Retrieve category Url from the document source.
     * Done from the document source to prevent having to use addUrlRewrite to result on category collection.
     *
     * @param array $documentSource Document Source.
     *
     * @return string
     */
    private function getCategoryUrl($documentSource)
    {
        if ($documentSource && isset($documentSource['url_path'])) {
            $urlPath = is_array($documentSource['url_path']) ? current($documentSource['url_path']) : $documentSource['url_path'];

            $url = trim($urlPath, '/') . $this->categoryUrlSuffix;

            return $url;
        }

        return '';
    }
}
