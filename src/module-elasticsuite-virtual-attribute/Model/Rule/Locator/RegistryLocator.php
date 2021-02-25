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
namespace Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator;

/**
 * Rule Registry Locator.
 * Used by Ui Component modifiers.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var null
     */
    private $rule = null;

    /**
     * RegistryLocator constructor.
     *
     * @param \Magento\Framework\Registry $registry The registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getRule()
    {
        if (null !== $this->rule) {
            return $this->rule;
        }

        if ($rule = $this->registry->registry('current_rule')) {
            return $this->rule = $rule;
        }

        return null;
    }
}
