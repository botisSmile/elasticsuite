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
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
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
 *
 * TODO ribay@smile.fr extend \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Limitation
 *      w/ saveLimitation and _construct being overriden ?
 */
class CustomerSegment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
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
