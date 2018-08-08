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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule\Condition\Sql;

/**
 * SQL Builder for rules.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Builder extends \Magento\Rule\Model\Condition\Sql\Builder
{
    /**
     * @var array
     */
    protected $_conditionOperatorMap = [
        '=='     => ':field = ?',
        '!='     => ':field <> ?',
        '>='     => ':field >= ?',
        '>'      => ':field > ?',
        '<='     => ':field <= ?',
        '<'      => ':field < ?',
        '{}'     => ':field IN (?)',
        '!{}'    => ':field NOT IN (?)',
        '()'     => ':field IN (?)',
        '!()'    => ':field NOT IN (?)',
        'like'   => ':field LIKE ?',
        'nlike'  => ':field NOT LIKE ?',
        'finset' => 'FIND_IN_SET(?, :field)',
    ];

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface|null
     */
    private $attributeRepository;

    /**
     * @param \Magento\Rule\Model\Condition\Sql\ExpressionFactory $expressionFactory   Expression Factory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface|null  $attributeRepository Attribute Repository
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Sql\ExpressionFactory $expressionFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($expressionFactory);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritdoc}
     */
    protected function _getMappedSqlCondition(
        \Magento\Rule\Model\Condition\AbstractCondition $condition,
        $value = '',
        $isDefaultStoreUsed = true
    ) {
        $argument = $condition->getMappedSqlField();

        // If rule hasn't valid argument - create negative expression to prevent incorrect rule behavior.
        if (empty($argument)) {
            return $this->_expressionFactory->create(['expression' => '1 = -1']);
        }

        $conditionOperator = $this->getOperator($condition);

        if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
        }

        $defaultValue = 0;
        // Check if attribute has a table with default value and add it to the query.
        if ($this->canAttributeHaveDefaultValue($condition->getAttribute(), $isDefaultStoreUsed)) {
            $defaultField = 'at_' . $condition->getAttribute() . '_default.value';
            $defaultValue = $this->_connection->quoteIdentifier($defaultField);
        }

        $sql = str_replace(
            ':field',
            $this->_connection->getIfNullSql($this->_connection->quoteIdentifier($argument), $defaultValue),
            $this->_conditionOperatorMap[$conditionOperator]
        );

        $expression = $this->_expressionFactory->create(
            ['expression' => $value . $this->_connection->quoteInto($sql, $this->getBindValue($condition))]
        );

        // Manage computation against multiple select attributes.
        if ($this->isArray($condition->getAttribute()) && in_array($conditionOperator, ['()', '!()'])) {
            $values = $condition->getBindArgumentValue();
            $values = is_string($values) ? explode(',', $values) : $values;

            $clauses = [];
            foreach ($values as $distinctValue) {
                $sql = str_replace(
                    ':field',
                    $this->_connection->getIfNullSql($this->_connection->quoteIdentifier($argument), $defaultValue),
                    $this->_conditionOperatorMap['finset']
                );

                $clauses[] = $this->_expressionFactory->create(
                    ['expression' => $value . $this->_connection->quoteInto($sql, $distinctValue)]
                );
            }
            $expression = implode(' OR ', $clauses);

            if (strpos($conditionOperator, '!') !== false) {
                $expression = 'NOT ' . implode(' AND NOT ', $clauses);
            }
        }

        return $expression;
    }

    /**
     * Get SQL operator for a condition.
     * Overridden to manage strings.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $condition The condition
     *
     * @return string
     */
    private function getOperator(\Magento\Rule\Model\Condition\AbstractCondition $condition)
    {
        $conditionOperator = $condition->getOperatorForValidate();

        try {
            if ($this->isString($condition->getAttribute()) && in_array($conditionOperator, ['{}', '!{}'])) {
                $conditionOperator = 'like';
                if (strpos($condition->getOperatorForValidate(), '!') !== false) {
                    $conditionOperator = 'nlike';
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $conditionOperator;
        }

        return $conditionOperator;
    }

    /**
     * Get value to bind in SQL query.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $condition The condition
     *
     * @return array|float|int|mixed|string
     */
    private function getBindValue(\Magento\Rule\Model\Condition\AbstractCondition $condition)
    {
        $value = $condition->getBindArgumentValue();

        if (in_array($this->getOperator($condition), ['like', 'nlike'])) {
            $value = '%' . $value . '%';
        }

        return $value;
    }

    /**
     * Test if an attribute is a string.
     *
     * @param string $attributeCode The attribute code
     *
     * @return bool
     */
    private function isString($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

            return (
                in_array($attribute->getBackendType(), ['varchar', 'text'])
                && in_array($attribute->getFrontendInput(), ['text', 'textarea'])
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Test if an attribute is an array. (for multiselect attributes).
     *
     * @param string $attributeCode The attribute code
     *
     * @return bool
     */
    private function isArray($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

            return (
                in_array($attribute->getBackendType(), ['varchar', 'text'])
                && in_array($attribute->getFrontendInput(), ['multiselect'])
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Check if attribute can have default value.
     *
     * Copy-pasted here since member has private access.
     *
     * @param string $attributeCode      Attribute Code
     * @param bool   $isDefaultStoreUsed Is default store used
     *
     * @return bool
     */
    private function canAttributeHaveDefaultValue(string $attributeCode, bool $isDefaultStoreUsed): bool
    {
        if ($isDefaultStoreUsed) {
            return false;
        }

        try {
            $attribute = $this->attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // It's not exceptional case as we want to check if we have such attribute or not.
            return false;
        }

        return !$attribute->isScopeGlobal();
    }
}
