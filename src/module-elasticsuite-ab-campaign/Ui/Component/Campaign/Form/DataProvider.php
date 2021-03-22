<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Campaign\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;

/**
 * Data Provider for UI components
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $modifierPool;

    /**
     * DataProvider constructor
     *
     * @param string                    $name                      Component Name
     * @param string                    $primaryFieldName          Primary Field Name
     * @param string                    $requestFieldName          Request Field Name
     * @param CampaignCollectionFactory $campaignCollectionFactory Campaign Collection Factory
     * @param PoolInterface             $modifierPool              Modifiers Pool
     * @param array                     $meta                      Component Metadata
     * @param array                     $data                      Component Data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CampaignCollectionFactory $campaignCollectionFactory,
        PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection   = $campaignCollectionFactory->create();
        $this->modifierPool = $modifierPool;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        foreach ($this->getCollection()->getItems() as $itemId => $item) {
            // Ensure optimizer config and rule get properly instantiated.
            $item->getResource()->afterLoad($item);
            $this->data[$itemId] = $item->toArray();
        }

        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();

        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }
}
