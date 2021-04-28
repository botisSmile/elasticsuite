<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Plugin\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Smile\ElasticsuiteCatalogOptimizer\Api\OptimizerRepositoryInterface;
use Smile\ElasticsuiteCatalogOptimizer\Block\Adminhtml\Optimizer\RuleCondition as Subject;

/**
 * Plugin OptimizerRuleConditionBlockPlugin: Adapt getOptimizer method for the campaign page.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class OptimizerRuleConditionBlockPlugin
{
    /**
     * @var OptimizerRepositoryInterface
     */
    private $optimizerRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * OptimizerRuleConditionBlockPlugin constructor.
     *
     * @param OptimizerRepositoryInterface $optimizerRepository Campaign optimizer manager
     * @param RequestInterface             $request             Request
     * @param Registry                     $registry            Registry
     */
    public function __construct(
        OptimizerRepositoryInterface $optimizerRepository,
        RequestInterface $request,
        Registry $registry
    ) {
        $this->optimizerRepository = $optimizerRepository;
        $this->registry            = $registry;
        $this->request             = $request;
    }

    /**
     * Register the optimizer before rendering in campaign page.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Subject $subject Rule Condition BLock
     * @return array
     */
    public function beforeToHtml(Subject $subject): array
    {
        $isInCampaignPage = (int) $this->request->getParam('campaign_id');
        $optimizerId = (int) $this->request->getParam('id');
        if ($isInCampaignPage && $optimizerId) {
            try {
                $this->registry->register('current_optimizer', $this->optimizerRepository->getById($optimizerId));
            } catch (NoSuchEntityException $noSuchEntityException) {
                // Do nothing.
            }
        }

        return [];
    }
}
