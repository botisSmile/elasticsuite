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
namespace Smile\ElasticsuiteVirtualAttribute\Model;

/**
 * Elastic Suite Virtual Attribute Rule model.
 * @SuppressWarnings(CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Rule extends \Magento\Framework\Model\AbstractModel implements \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'smile_elasticsuite_virtual_attribute';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Rule constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context            Model Context
     * @param \Magento\Framework\Registry                                  $registry           Registry
     * @param \Magento\CatalogRule\Model\RuleFactory                       $ruleFactory        Rule Factory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource           Resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection Resource Collection
     * @param array                                                        $data               Data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getId() : int
    {
        return (int) $this->getData(self::RULE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeId() : int
    {
        return (int) $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId() : int
    {
        return (int) $this->getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive() : bool
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority() : int
    {
        return (int) $this->getData(self::PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function isToRefresh() : bool
    {
        return (bool) $this->getData(self::TO_REFRESH);
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        if (!is_object($this->getData(self::CONDITION))) {
            $ruleData = $this->getData(self::CONDITION);
            $rule     = $this->ruleFactory->create();

            if (is_string($ruleData)) {
                $ruleData = unserialize($ruleData);
            }

            if (is_array($ruleData)) {
                $rule->getConditions()->loadArray($ruleData);
            }

            $this->setData(self::CONDITION, $rule);
        }

        return $this->getData(self::CONDITION);
    }

    /**
     * {@inheritdoc}
     */
    public function getStores() : array
    {
        $stores = $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');

        if (is_numeric($stores)) {
            $stores = [$stores];
        }

        return $stores ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setId($ruleId)
    {
        return $this->setData(self::RULE_ID, (int) $ruleId);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeId(int $attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionId(int $optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive(bool $status)
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority(int $priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function setToRefresh(bool $toRefresh)
    {
        return $this->setData(self::TO_REFRESH, $toRefresh);
    }

    /**
     * {@inheritdoc}
     */
    public function setCondition($ruleCondition)
    {
        return $this->setData(self::CONDITION, $ruleCondition);
    }

    /**
     * {@inheritdoc}
     */
    public function loadPost($data = [])
    {
        $this->addData($data);

        if (isset($data[self::CONDITION])) {
            $this->getCondition()->loadPost($data[self::CONDITION]);
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule');
    }
}
