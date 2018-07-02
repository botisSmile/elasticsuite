<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Ui\Component\Rule\Form;

/**
 * Smile Elastic Suite Virtual Attribute rule edit form data provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Ui\DataProvider\Modifier\PoolInterface
     */
    private $modifierPool;

    /**
     * DataProvider constructor
     *
     * @param string                                                                         $name                  Component Name
     * @param string                                                                         $primaryFieldName      Primary Field Name
     * @param string                                                                         $requestFieldName      Request Field Name
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory Rule Collection Factory
     * @param \Magento\Ui\DataProvider\Modifier\PoolInterface                                $modifierPool          Modifiers Pool
     * @param array                                                                          $meta                  Component Metadata
     * @param array                                                                          $data                  Component Data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Ui\DataProvider\Modifier\PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection   = $ruleCollectionFactory->create();
        $this->modifierPool = $modifierPool;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        foreach ($this->getCollection()->getItems() as $itemId => $item) {
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
