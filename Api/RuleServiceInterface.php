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
 * Virtual Attribute Rule Service interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface RuleServiceInterface
{
    /**
     * Refresh a list of rule Ids.
     *
     * @param array $ruleIds The rule ids
     *
     * @return void
     */
    public function refresh(array $ruleIds);

    /**
     * Retrieve product Ids matching the rule for a given store.
     *
     * @param int $ruleId  The rule Id
     * @param int $storeId The store Id
     *
     * @return array
     */
    public function getMatchingProductIds(int $ruleId, int $storeId);
}
