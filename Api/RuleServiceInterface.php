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
     * Set a list of rule Ids to be refreshed.
     *
     * @param array $ruleIds The rule ids
     *
     * @return void
     */
    public function scheduleRefresh(array $ruleIds);

    /**
     * Process appliance of all rules set to be refreshed.
     *
     * @return void
     */
    public function processRefresh();
}
