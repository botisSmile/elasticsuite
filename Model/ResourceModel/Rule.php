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
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Smile Elastic Suite Virtual Attribute rule resource model.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    private $ruleFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        $connectionName = null
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $rule          = $this->ruleFactory->create();
        $ruleCondition = $object->getCondition();

        if (is_object($ruleCondition)) {
            $rule = $ruleCondition;
        } elseif (is_array($ruleCondition)) {
            $rule->getConditions()->loadArray($ruleCondition);
        }

        $object->setCondition(serialize($rule->getConditions()->asArray()));

        return parent::_beforeSave($object);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /* Using getter to force unserialize*/
        $object->getCondition();

        return parent::_afterLoad($object);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(RuleInterface::TABLE_NAME, RuleInterface::RULE_ID);
    }
}
