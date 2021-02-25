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

namespace Smile\ElasticsuiteVirtualAttribute\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run rules applications (same as the cronjob)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ApplyRules extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface
     */
    private $service;

    /**
     * ApplyRules constructor.
     *
     * @param \Magento\Framework\App\State                                 $appState App state
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface $service  Rules Service
     * @param string                                                       $name     Command name
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Smile\ElasticsuiteVirtualAttribute\Api\RuleServiceInterface $service,
        $name = null
    ) {
        $this->appState = $appState;
        $this->service  = $service;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('elasticsuite:virtualattributes:apply');
        $this->setDescription('Apply ElasticSuite virtual attributes rules.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');
        $this->service->processRefresh();
    }
}
