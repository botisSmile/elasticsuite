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
namespace Smile\ElasticsuiteVirtualAttribute\Cron;

/**
 * Cron Job that will trigger rules computation.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ApplyRules
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface
     */
    private $service;

    /**
     * ApplyRules constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface $service Rules Service
     */
    public function __construct(\Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Apply rules for all stores.
     *
     * @return void
     */
    public function execute()
    {
        $this->service->processRefresh();
    }
}
