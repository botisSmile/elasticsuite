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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Custom Hydrator for Virtual Attribute rules.
 * Used to serialize/unserialize values.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Hydrator implements \Magento\Framework\EntityManager\HydratorInterface
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Hydrator constructor.
     *
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer  Serializer
     * @param \Magento\CatalogRule\Model\RuleFactory           $ruleFactory Rule Factory
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory
    ) {
        $this->serializer  = $serializer;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($entity)
    {
        $data = $entity->getData();

        $rule          = $this->ruleFactory->create();
        $ruleCondition = $data[RuleInterface::CONDITION];

        if (is_string($ruleCondition)) {
            $ruleCondition = $this->serializer->unserialize($ruleCondition);
        }

        if (is_object($ruleCondition)) {
            $rule = $ruleCondition;
        } elseif (is_array($ruleCondition)) {
            $rule->getConditions()->loadArray($ruleCondition);
        }

        $data[RuleInterface::CONDITION] = $this->serializer->serialize($rule->getConditions()->asArray());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($entity, array $data)
    {
        $entity->setData(array_merge($entity->getData(), $data));

        if (!is_object($data[RuleInterface::CONDITION])) {
            $ruleData = $data[RuleInterface::CONDITION];
            $rule     = $this->ruleFactory->create();

            if (is_string($ruleData)) {
                $ruleData = $this->serializer->unserialize($ruleData);
            }

            if (is_array($ruleData)) {
                $rule->getConditions()->loadArray($ruleData);
            }

            $entity->setData(RuleInterface::CONDITION, $rule);
        }

        return $entity;
    }
}
