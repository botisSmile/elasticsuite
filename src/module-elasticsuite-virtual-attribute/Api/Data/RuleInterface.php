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

namespace Smile\ElasticsuiteVirtualAttribute\Api\Data;

/**
 * Elasticsuite Virtual Attribute Rule Interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface RuleInterface
{
    /**
     * Name of the main Mysql Table
     */
    const TABLE_NAME = 'smile_elasticsuite_virtual_attribute_rule';

    /**
     * Name of the rule-store association table
     */
    const STORE_TABLE_NAME = 'smile_elasticsuite_virtual_attribute_rule_store';

    /**
     * Constant for field rule_id
     */
    const RULE_ID = 'rule_id';

    /**
     * Constant for field attribute_id
     */
    const ATTRIBUTE_ID = 'attribute_id';

    /**
     * Constant for field option_id
     */
    const OPTION_ID = 'option_id';

    /**
     * Constant for field is_active
     */
    const IS_ACTIVE = 'is_active';

    /**
     * Constant for priority field
     */
    const PRIORITY = 'priority';

    /**
     * Constant for to_refresh field
     */
    const TO_REFRESH = 'to_refresh';

    /**
     * Constant for field condition
     */
    const CONDITION = 'condition';

    /**
     * Get Optimizer ID
     *
     * @return int|null
     */
    public function getId() : int;

    /**
     * Get attribute Id
     *
     * @return int
     */
    public function getAttributeId() : int;

    /**
     * Get option Id
     *
     * @return int
     */
    public function getOptionId() : int;

    /**
     * Get Rule status
     *
     * @return bool
     */
    public function isActive() : bool;

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority() : int;

    /**
     * Get Rule to_refresh status flag
     *
     * @return bool
     */
    public function isToRefresh() : bool;

    /**
     * Get rule_condition
     *
     * @return \Magento\CatalogRule\Model\Rule
     */
    public function getCondition();

    /**
     * Retrieve rule store ids
     *
     * @return int[]
     */
    public function getStores() : array;

    /**
     * Set rule id
     *
     * @param int $ruleId Rule id.
     *
     * @return RuleInterface
     */
    public function setId($ruleId);

    /**
     * Set attribute Id.
     *
     * @param int $attributeId The attribute Id
     *
     * @return RuleInterface
     */
    public function setAttributeId(int $attributeId);

    /**
     * Set attribute option Id.
     *
     * @param int $optionId The option Id
     *
     * @return RuleInterface
     */
    public function setOptionId(int $optionId);

    /**
     * Set Rule status
     *
     * @param bool $status The Rule status
     *
     * @return RuleInterface
     */
    public function setIsActive(bool $status);

    /**
     * Set rule priority.
     *
     * @param int $priority The priority
     *
     * @return RuleInterface
     */
    public function setPriority(int $priority);

    /**
     * Set Rule to_refresh flag field.
     *
     * @param bool $toRefresh The Rule status
     *
     * @return RuleInterface
     */
    public function setToRefresh(bool $toRefresh);

    /**
     * Set condition
     *
     * @param string $ruleCondition The condition.
     *
     * @return string
     */
    public function setCondition($ruleCondition);

    /**
     * Load data from a POST request.
     *
     * @param array $data POST data
     *
     * @return RuleInterface
     */
    public function loadPost($data = []);
}
