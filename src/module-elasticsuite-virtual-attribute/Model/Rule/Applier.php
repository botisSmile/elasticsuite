<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule;

use \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\MatcherFactory as RuleMatcherFactory;
use \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier\ValueUpdaterFactory as RuleValueUpdaterFactory;

/**
 * Elastic Suite Virtual Attribute Rule applier.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Applier
{
    /**
     * @var \Magento\CatalogRule\Model\Rule
     */
    private $condition;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private $attribute;

    /**
     * @var integer
     */
    private $optionId;

    /**
     * @var boolean
     */
    private $ruleStatus;

    /**
     * @var integer
     */
    private $storeId;

    /**
     * Applier constructor.
     *
     * @param RuleMatcherFactory                                  $matcherFactory      Rule Matcher Factory
     * @param RuleValueUpdaterFactory                             $valueUpdaterFactory Product Value Updater Factory
     * @param \Magento\CatalogRule\Model\Rule                     $condition           The rule condition to match on
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute           The attribute to apply value for
     * @param int                                                 $optionId            The value to apply
     * @param bool                                                $ruleStatus          The rule status
     * @param int                                                 $storeId             The storeId to apply value for
     */
    public function __construct(
        RuleMatcherFactory $matcherFactory,
        RuleValueUpdaterFactory $valueUpdaterFactory,
        \Magento\CatalogRule\Model\Rule $condition,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        $optionId,
        $ruleStatus,
        $storeId
    ) {
        $this->condition  = $condition;
        $this->attribute  = $attribute;
        $this->optionId   = $optionId;
        $this->storeId    = $storeId;
        $this->ruleStatus = $ruleStatus;

        $this->matcher = $matcherFactory->create([
            'attribute' => $this->attribute,
            'optionId'  => $this->optionId,
            'storeId'   => $this->storeId,
            'condition' => $this->condition,
        ]);

        $this->valueUpdater = $valueUpdaterFactory->create([
            'attribute' => $this->attribute,
            'optionId'  => $this->optionId,
            'storeId'   => $this->storeId,
        ]);
    }

    /**
     * Apply current condition for attribute and option Id.
     *
     * @throws \Exception
     */
    public function apply()
    {
        // Remove value for products having it previously.
        $this->deletePreviousMatching();

        if ($this->isEnabledRule()) {
            $this->updateMatchingProducts();
        }
    }

    /**
     * Process a cleanup of temporary data to remove completely the current option Id.
     * Called when a rule is disabled.
     */
    public function cleanup()
    {
        if (!$this->isEnabledRule()) {
            $this->valueUpdater->removeOptionId();
        }
    }

    /**
     * Compute data for products actually matching the conditions.
     */
    public function updateMatchingProducts()
    {
        // Add value for products that are now matching the rules.
        $updateCount = 0;
        foreach ($this->matcher->matchByCondition() as $row) {
            $this->valueUpdater->updateValue($row);
            $updateCount++;
            if ($updateCount % 1000 === 0) {
                $this->valueUpdater->persist();
            }
        }
        $this->valueUpdater->persist();
    }

    /**
     * Remove value for products having it previously.
     * They may not match anymore the condition.
     */
    private function deletePreviousMatching()
    {
        $deleteCount = 0;
        foreach ($this->matcher->matchByOptionId() as $row) {
            $this->valueUpdater->removeValue($row);
            $deleteCount++;
            if ($deleteCount % 1000 === 0) {
                $this->valueUpdater->persist();
            }
        }
        $this->valueUpdater->persist();
    }

    /**
     * Check if rule associated to this applier is enabled.
     *
     * @return bool
     */
    private function isEnabledRule()
    {
        return (bool) $this->ruleStatus === true;
    }
}
