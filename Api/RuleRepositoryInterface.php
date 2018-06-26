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

namespace Smile\ElasticsuiteVirtualAttribute\Api;

/**
 * Virtual Attribute Rule Repository interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface RuleRepositoryInterface
{
    /**
     * Retrieve a rule by its ID
     *
     * @param int $ruleId Id of the rule.
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($ruleId);

    /**
     * Retrieve list of rule
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
     */
    public function getList();

    /**
     * save a rule
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule Rule
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule);

    /**
     * delete a rule
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule Rule
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface $rule);

    /**
     * Remove rule by given ID
     *
     * @param int $ruleId Id of the rule.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function deleteById($ruleId);
}
