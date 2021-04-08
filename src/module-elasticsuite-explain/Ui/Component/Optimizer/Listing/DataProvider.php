<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Ui\Component\Optimizer\Listing;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider as BaseDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory;
use Smile\ElasticsuiteExplain\Model\Renderer\Optimizer as OptimizerRenderer;

/**
 * Optimizer listing data provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class DataProvider extends BaseDataProvider
{
    /**
     * @var OptimizerRenderer
     */
    protected $optimizerRenderer;

    /**
     * @var OptimizerCollectionProcessorInterface[]
     */
    protected $collectionProcessors;

    /**
     * @var PoolInterface
     */
    protected $modifierPool;

    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * DataProvider Constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param string                                  $name                 Component name
     * @param string                                  $primaryFieldName     Primary field Name
     * @param string                                  $requestFieldName     Request field name
     * @param CollectionFactory                       $collectionFactory    The collection factory
     * @param OptimizerRenderer                       $optimizerRenderer    Optimizer Renderer
     * @param PoolInterface                           $modifierPool         Modifier Pool
     * @param OptimizerCollectionProcessorInterface[] $collectionProcessors Collection processors
     * @param AddFieldToCollectionInterface[]         $addFieldStrategies   Add field Strategy
     * @param AddFilterToCollectionInterface[]        $addFilterStrategies  Add filter Strategy
     * @param array                                   $meta                 Component Meta
     * @param array                                   $data                 Component extra data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        OptimizerRenderer $optimizerRenderer,
        PoolInterface $modifierPool,
        array $collectionProcessors = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection           = $collectionFactory->create();
        $this->optimizerRenderer    = $optimizerRenderer;
        $this->collectionProcessors = $collectionProcessors;
        $this->addFieldStrategies   = $addFieldStrategies;
        $this->addFilterStrategies  = $addFilterStrategies;
        $this->modifierPool         = $modifierPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var OptimizerCollection $collection */
        $collection = $this->getCollection();

        // Apply processors to the optimizer collection.
        if (!$collection->isLoaded()) {
            $collection->addFieldToSelect(['config', 'rule_condition']);
            foreach ($this->collectionProcessors as $collectionProcessor) {
                $collectionProcessor->process($collection);
            }
        }

        $itemsData = [];
        /** @var Optimizer $optimizer */
        foreach ($collection->getItems() as $optimizer) {
            $itemsData[] = array_merge(
                $optimizer->toArray(),
                [
                    'boost' => $this->optimizerRenderer->renderBoost($optimizer),
                    'rule'  => $this->optimizerRenderer->renderRuleConditions($optimizer),
                ]
            );
        }

        return ['totalRecords' => count($itemsData), 'items' => $itemsData];
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();

        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }

    /**
     * {@inheritDoc}
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);

            return ;
        }

        parent::addField($field, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );

            return;
        }

        parent::addFilter($filter);
    }
}
