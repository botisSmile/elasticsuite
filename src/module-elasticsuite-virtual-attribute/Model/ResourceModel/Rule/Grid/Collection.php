<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Grid;

use \Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Smile Elastic Suite Virtual Attribute rule collection for grids.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Collection extends \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Collection implements SearchResultInterface
{
    /**
     * @var \Magento\Framework\Api\Search\AggregationInterface[]
     */
    private $aggregations;

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setModel('Magento\Framework\View\Element\UiComponent\DataProvider\Document');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->getSelect()->joinInner(
            ['ea' => $this->getTable('eav_attribute')],
            'ea.attribute_id = main_table.attribute_id',
            ['frontend_label']
        );


        $this->getSelect()->joinLeft(
            ['eaov' => $this->getTable('eav_attribute_option_value')],
            'eaov.option_id = main_table.option_id',
            ['option_label' => 'value']
        );

        $storeCondition = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        if ($this->getFilter('store')) {
            $storeId = current($this->getStoreIds());

            $this->getSelect()->joinLeft(
                ['eaov_s' => $this->getTable('eav_attribute_option_value')],
                sprintf('eaov_s.option_id = main_table.option_id AND eaov_s.store_id = %s', $storeId),
                ['option_label' => 'value']
            );

            $storeCondition = $this->getConnection()->getIfNullSql(
                "eaov_s.store_id",
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
        }

        $this->getSelect()->where('eaov.store_id = ?', $storeCondition);

        return $this;
    }
}