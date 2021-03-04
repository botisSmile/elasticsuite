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

namespace Smile\ElasticsuiteExplain\Ui\Component\Explain\Form;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Data provider for adminhtml explain form
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * Search result object.
     *
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * Search criteria object.
     *
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * Own name of this provider.
     *
     * @var string
     */
    private $name;

    /**
     * Provider configuration data.
     *
     * @var array
     */
    private $data;

    /**
     * Provider configuration meta.
     *
     * @var array
     */
    private $meta;

    /**
     * @param string                  $name           Component name
     * @param SearchResultInterface   $searchResult   Search results - unused
     * @param SearchCriteriaInterface $searchCriteria Search criteria - unused
     * @param PoolInterface           $pool           Modifiers pool
     * @param array                   $meta           Component metadata
     * @param array                   $data           Component data
     */
    public function __construct(
        $name,
        SearchResultInterface $searchResult,
        SearchCriteriaInterface $searchCriteria,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->name           = $name;
        $this->searchResult   = $searchResult;
        $this->searchCriteria = $searchCriteria;
        $this->pool           = $pool;
        $this->meta           = $meta;
        $this->data           = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData()
    {
        return $this->data['config'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;

        return true;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryFieldName()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestFieldName()
    {
        return 'explain_id';
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        // Dummy. This component is not a real CRUD form nor a grid.
    }

    /**
     * {@inheritdoc}
     */
    public function addOrder($field, $direction)
    {
        // Dummy. This component is not a real CRUD form nor a grid.
    }

    /**
     * {@inheritdoc}
     */
    public function setLimit($offset, $size)
    {
        // Dummy. This component is not a real CRUD form nor a grid.
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        // Dummy. This component is not a real CRUD form nor a grid.
        return $this->searchCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResult()
    {
        // Dummy. This component is not a real CRUD form nor a grid.
        return $this->searchResult;
    }
}
