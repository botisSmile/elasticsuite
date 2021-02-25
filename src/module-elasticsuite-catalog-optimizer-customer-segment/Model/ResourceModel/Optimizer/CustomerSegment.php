<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Model\ResourceModel\Optimizer;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Api\Data\OptimizerCustomerSegmentInterface;

/**
 * Optimizer Customer Segment Resource Model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class CustomerSegment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Return the list of applicable optimizer ids according to given list of customer segment ids.
     * Union of the list of optimizers without any segment limitation and the list of optimizers
     * with a specific limitation on the provided segments.
     *
     * @param array $segmentIds Customer segment ids.
     *
     * @return array
     */
    public function getApplicableOptimizerIdsByCustomerSegmentIds($segmentIds = [])
    {
        $selects = [];

        $selects[] = $this->getConnection()
            ->select()
            ->from(['o' => $this->getTable(OptimizerInterface::TABLE_NAME)], [OptimizerInterface::OPTIMIZER_ID])
            ->joinLeft(
                ['main_table' => $this->getMainTable()],
                sprintf("o.%s = main_table.%s", OptimizerInterface::OPTIMIZER_ID, OptimizerCustomerSegmentInterface::OPTIMIZER_ID),
                []
            )
            ->where(
                sprintf("main_table.%s IS NULL", OptimizerCustomerSegmentInterface::OPTIMIZER_ID)
            )
            ->group(OptimizerInterface::OPTIMIZER_ID);

        if (!empty($segmentIds)) {
            $selects[] = $this->getConnection()
                ->select()
                ->from(
                    ['main_table' => $this->getTable(OptimizerCustomerSegmentInterface::TABLE_NAME)],
                    [OptimizerInterface::OPTIMIZER_ID]
                )
                ->where(
                    $this->getConnection()->quoteInto(
                        sprintf('main_table.%s IN (?)', OptimizerCustomerSegmentInterface::SEGMENT_ID),
                        $segmentIds
                    )
                )
                ->group(OptimizerCustomerSegmentInterface::OPTIMIZER_ID);
        }

        $select = $this->getConnection()->select()->union($selects);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve all customer segments associated to a given optimizer.
     *
     * @param OptimizerInterface $optimizer The optimizer
     *
     * @return array
     */
    public function getSegmentIdsByOptimizer(OptimizerInterface $optimizer)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), OptimizerCustomerSegmentInterface::SEGMENT_ID)
            ->where($this->getConnection()->quoteInto(OptimizerInterface::OPTIMIZER_ID . " = ?", (int) $optimizer->getId()));

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Save customer segment data for a given optimizer.
     *
     * @param OptimizerInterface $optimizer      The optimizer.
     * @param array              $limitationData An array containing segment limitation data to save.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveLimitation($optimizer, $limitationData)
    {
        $rows        = [];
        $optimizerId = (int) $optimizer->getId();

        $this->getConnection()->delete(
            $this->getMainTable(),
            $this->getConnection()->quoteInto(OptimizerCustomerSegmentInterface::OPTIMIZER_ID . " = ?", $optimizerId)
        );

        $fields = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($limitationData as $item) {
            $item[$this->getIdFieldName()] = $optimizerId;
            $rows[] = array_replace(array_fill_keys(array_keys($fields), null), array_intersect_key($item, $fields));
        }

        $result = true;
        if (!empty($rows)) {
            $result = (bool) $this->getConnection()->insertArray($this->getMainTable(), array_keys($fields), $rows);
        }

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(OptimizerCustomerSegmentInterface::TABLE_NAME, OptimizerCustomerSegmentInterface::OPTIMIZER_ID);
    }
}
