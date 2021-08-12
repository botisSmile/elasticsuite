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

namespace Smile\ElasticsuiteExplain\Plugin\Index;

use Magento\Store\Api\Data\StoreInterface;
use Smile\ElasticsuiteCore\Api\Client\ClientInterface;
use Smile\ElasticsuiteCore\Api\Index\IndexInterface;
use Smile\ElasticsuiteCore\Api\Index\IndexOperationInterface;
use Smile\ElasticsuiteExplain\Model\Indexer\Optimizer\Percolator;

/**
 * Plugin to append standard index mapping into optimizer index.
 * Optimizer index must be aware of product-related indices mapping.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Gauthier <pierre.gauthier@smile.fr>
 */
class IndexOperation
{
    /** @var ClientInterface */
    private $client;

    /**
     * IndexOperation constructor.
     *
     * @param ClientInterface $client Elasticsearch client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param IndexOperationInterface       $subject         Index Operation
     * @param IndexInterface                $result          Resulting index
     * @param string                        $indexIdentifier Index identifier
     * @param integer|string|StoreInterface $store           Store (id, identifier or object).
     *
     * @return IndexInterface
     */
    public function afterCreateIndex(
        IndexOperationInterface $subject,
        IndexInterface $result,
        string $indexIdentifier,
        $store
    ) {
        // If we are rebuilding the optimizer index, we have to merge his mapping with the existing "product" mapping.
        if ($indexIdentifier === Percolator::INDEX_IDENTIFIER) {
            if ($subject->indexExists('catalog_product', $store)) {
                $productIndex = $subject->getIndexByName('catalog_product', $store);
                foreach ($result->getTypes() as $currentType) {
                    $this->client->putMapping(
                        $result->getName(),
                        $currentType->getName(),
                        $productIndex->getMapping()->asArray()
                    );
                }
            }
        }

        return $result;
    }
}
