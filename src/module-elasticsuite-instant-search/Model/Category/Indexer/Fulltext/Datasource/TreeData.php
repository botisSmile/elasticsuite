<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Category\Indexer\Fulltext\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;

/**
 * Category custom data source to index category tree.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class TreeData implements DatasourceInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResource;

    /**
     * @var array
     */
    private $categoryNames = [];

    /**
     * TreeData constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource Category Resource Model
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Category $categoryResource)
    {
        $this->categoryResource = $categoryResource;
    }

    /**
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData)
    {
        foreach ($indexData as $categoryId => $categoryData) {
            $indexData[$categoryId]['tree'] = $this->getCategoryBreadcrumb($categoryData, $storeId, $indexData);
        }

        return $indexData;
    }

    /**
     * Return a mini-breadcrumb for a category
     *
     * @param array $categoryData The Category Data
     * @param int   $storeId      The Store Id
     * @param array $indexData    Category data being indexed
     *
     * @return array
     */
    private function getCategoryBreadcrumb(array $categoryData, $storeId, $indexData)
    {
        $path    = $categoryData['path'] ?? '';
        $rawPath = explode('/', $path);

        // First occurence is root category (1), second is root category of store.
        $rawPath = array_slice($rawPath, 2);

        $breadcrumb = [];
        foreach ($rawPath as $categoryId) {
            $breadcrumb[] = html_entity_decode($this->getCategoryNameById($categoryId, $storeId, $indexData));
        }

        return $breadcrumb;
    }

    /**
     * Retrieve a category name by it's id, and store it in local cache
     *
     * @param int   $categoryId The category Id
     * @param int   $storeId    The store Id
     * @param array $indexData  The categories being indexed, to fetch names from other categories from it.
     *
     * @return string
     */
    private function getCategoryNameById($categoryId, $storeId, $indexData)
    {
        if (!isset($this->categoryNames[$categoryId])) {
            $result = false;

            if (isset($indexData[$categoryId]) && isset($indexData[$categoryId]['name'])) {
                $result = $indexData[$categoryId]['name'];
                if (is_array($result)) {
                    $result = current($result);
                }
            }

            if ($result === false) {
                $result = $this->categoryResource->getAttributeRawValue($categoryId, "name", $storeId);
            }

            $this->categoryNames[$categoryId] = $result;
        }

        return $this->categoryNames[$categoryId];
    }
}
