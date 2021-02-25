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
namespace Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Rule\Edit\Button;

/**
 * Generic button for virtual attribute rule edition.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AbstractButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\View\Element\UiComponent\Context
     */
    protected $context;

    /**
     * Rule Locator
     *
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * Generic constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\Context                     $context UI Component context
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator Rule Locator
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\Context $context,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator
    ) {
        $this->context = $context;
        $this->locator = $locator;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route  The route
     * @param array  $params The params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * Get Rule
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
     */
    public function getRule()
    {
        return $this->locator->getRule();
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
