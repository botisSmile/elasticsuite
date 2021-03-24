<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Block\Adminhtml\Campaign\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Abstract Campaign button
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class AbstractButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CampaignContext
     */
    protected $campaignContext;

    /**
     * @var CampaignManagerInterface
     */
    protected $campaignManager;

    /**
     * Abstract button constructor
     *
     * @param Context                  $context         Application context
     * @param CampaignContext          $campaignContext Campaign context
     * @param CampaignManagerInterface $campaignManager Campaign Manager
     */
    public function __construct(
        Context $context,
        CampaignContext $campaignContext,
        CampaignManagerInterface $campaignManager
    ) {
        $this->context = $context;
        $this->campaignContext = $campaignContext;
        $this->campaignManager = $campaignManager;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route  The route
     * @param array  $params The params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * Get Campaign
     *
     * @return CampaignInterface|null
     */
    public function getCampaign(): ?CampaignInterface
    {
        return $this->campaignContext->getCurrentCampaign();
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
