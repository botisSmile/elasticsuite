<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBeacon
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeacon\Model\ResourceModel\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class InsertIgnore
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class InsertIgnore
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * InsertIgnore constructor.
     *
     * @param ResourceConnection $resourceConnection Connection.
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Extract data from object and insert to columns of table
     *
     * @param array  $data      Data.
     * @param string $tableName Table name.
     * @param array  $columns   Columns.
     *
     * @return \Zend_Db_Statement_Interface
     */
    public function execute(array $data, string $tableName, array $columns): void
    {
        $data = $this->filterData($data, $columns);
        if (empty($data)) {
            return;
        }
        $query = sprintf(
            'INSERT IGNORE INTO `%s` (%s) VALUES (%s)',
            $this->resourceConnection->getTableName($tableName),
            $this->getColumns(array_keys($data)),
            $this->getValues(count($data))
        );

        $this->getConnection()->query($query, array_values($data));
    }

    /**
     * Filter data to keep only data for columns specified
     *
     * @param array $data    Data.
     * @param array $columns Columns.
     *
     * @return array
     */
    private function filterData(array $data, array $columns): array
    {
        return array_intersect_key($data, array_flip($columns));
    }

    /**
     * Retrieve DB adapter
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Get columns query part
     *
     * @param array $columns Columns.
     *
     * @return string
     */
    private function getColumns(array $columns): string
    {
        $connection = $this->getConnection();

        $sql = implode(', ', array_map([$connection, 'quoteIdentifier'], $columns));

        return $sql;
    }

    /**
     * Get values query part
     *
     * @param int $number Number of values.
     *
     * @return string
     */
    private function getValues(int $number): string
    {
        return implode(',', array_pad([], $number, '?'));
    }
}
