<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralData
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBehavioralData\Model\Product\Indexer\Fulltext\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;
use Smile\ElasticsuiteBehavioralData\Model\ResourceModel\Product\Indexer\Fulltext\Datasource\BehavioralData as ResourceModel;

/**
 * Behavioral Data indexing Datasource
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralData
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class BehavioralData implements DatasourceInterface
{
    /**
     * @var \Smile\ElasticsuiteBehavioralData\Model\ResourceModel\Product\Indexer\Fulltext\Datasource\BehavioralData
     */
    private $resourceModel;

    /**
     * BehavioralData constructor.
     *
     * @param ResourceModel $resourceModel Resource
     */
    public function __construct(ResourceModel $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData)
    {
        $behavioralData = $this->resourceModel->loadBehavioralData($storeId, array_keys($indexData));

        $indexData = array_replace_recursive($indexData, $behavioralData);

        return $indexData;
    }
}
