<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Optimizer;

use Elasticsearch\Client;
use Exception;
use LogicException;
use Magento\Framework\Profiler;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteCore\Api\Client\ClientConfigurationInterface;
use Smile\ElasticsuiteCore\Api\Index\IndexOperationInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterfaceFactory;
use Smile\ElasticsuiteCore\Client\ClientBuilder;
use Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\QueryResponseFactory;
use Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator as IndexerPercolator;
use Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator\Datasource\PercolatorData;

/**
 * Optimizers percolator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class Percolator
{
    /** @var Client */
    protected $client;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var IndexOperationInterface */
    protected $indexManager;

    /** @var LoggerInterface; */
    protected $logger;

    /** @var string */
    protected $indexIdentifier;

    /** @var QueryResponseFactory */
    private $responseFactory;

    /** @var ContainerConfigurationInterfaceFactory */
    private $containerConfigFactory;

    /**
     * Percolator constructor.
     *
     * @param ClientConfigurationInterface           $clientConfiguration    Client configuration factory.
     * @param ClientBuilder                          $clientBuilder          ES client builder.
     * @param StoreManagerInterface                  $storeManager           Store manager
     * @param IndexOperationInterface                $indexManager           ES index manager
     * @param QueryResponseFactory                   $queryResponseFactory   Search Query Response Factory
     * @param ContainerConfigurationInterfaceFactory $containerConfigFactory Search Request Container config Factory.
     * @param LoggerInterface                        $logger                 Logger
     * @param string                                 $indexIdentifier        ES index name/identifier (as defined in XMLs)
     */
    public function __construct(
        ClientConfigurationInterface $clientConfiguration,
        ClientBuilder $clientBuilder,
        StoreManagerInterface $storeManager,
        IndexOperationInterface $indexManager,
        QueryResponseFactory $queryResponseFactory,
        ContainerConfigurationInterfaceFactory $containerConfigFactory,
        LoggerInterface $logger,
        $indexIdentifier = IndexerPercolator::INDEX_IDENTIFIER
    ) {
        $this->client                 = $clientBuilder->build($clientConfiguration->getOptions());
        $this->storeManager           = $storeManager;
        $this->indexManager           = $indexManager;
        $this->responseFactory        = $queryResponseFactory;
        $this->logger                 = $logger;
        $this->indexIdentifier        = $indexIdentifier;
        $this->containerConfigFactory = $containerConfigFactory;
    }

    /**
     * Get the IDs of optimizers whose conditions a given product matches
     *
     * @param int      $productId       Product ID to find matching optimizer for
     * @param int|null $storeId         Store ID
     * @param string   $searchContainer Search container
     * @return int[]
     * @throws Exception
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getMatchingOptimizerIds(int $productId, int $storeId, string $searchContainer): array
    {
        $optimizersIds   = [];
        $index           = $this->indexManager->getIndexByName($this->indexIdentifier, $storeId);
        $containerConfig = $this->getRequestContainerConfiguration($storeId, 'catalog_view_container');

        $percolatorQuery['percolate'] = [
            'field' => 'query',
            'index' => $containerConfig->getIndexName(),
            'type' => 'product',
            'id'    => $productId,
        ];

        $percolatorFilter['query']['bool']['must'] = [
            ['term' => ['percolator_type' => PercolatorData::PERCOLATOR_TYPE]],
            ['term' => ['is_active' => true]],
            ['term' => ['search_container' => $searchContainer]],
            $percolatorQuery,
        ];

        Profiler::start('ES:Percolate Optimizers Document');
        try {
            $searchParams   = ['index' => $index->getName(), 'type' => 'optimizer', 'body' => $percolatorFilter];
            $searchResponse = $this->client->search($searchParams);
        } catch (Exception $e) {
            $searchResponse = [];
            $this->logger->error($e->getMessage());
        }
        Profiler::stop('ES:Percolate Optimizers Document');

        $response = $this->responseFactory->create(['searchResponse' => $searchResponse]);
        foreach ($response->getIterator() as $document) {
            $optimizersIds[] = $document->getId();
        }

        return $optimizersIds;
    }

    /**
     * Load the search request configuration (index, type, mapping, ...) using the search request container name.
     *
     * @param integer $storeId       Store id.
     * @param string  $containerName Search request container name.
     *
     * @return ContainerConfigurationInterface
     * @throws LogicException Thrown when the search container is not found into the configuration.
     */
    private function getRequestContainerConfiguration(int $storeId, string $containerName)
    : ContainerConfigurationInterface
    {
        $config = $this->containerConfigFactory->create(['containerName' => $containerName, 'storeId' => $storeId]);
        if ($config === null) {
            throw new LogicException("No configuration exists for request {$containerName}");
        }

        return $config;
    }
}
