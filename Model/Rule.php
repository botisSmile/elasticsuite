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
 *
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
    public function getCondition()
    {
        return $this->getData(self::CONDITION);
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
    public function setCondition($ruleCondition)
    {
        return $this->setData(self::CONDITION, $ruleCondition);
    }
}
