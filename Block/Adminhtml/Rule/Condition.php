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

namespace Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Rule;

/**
 * Form condition field renderer for virtual rule edition.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.Fr>
 */
class Condition extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface
     */
    private $locator;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Context                                          $context     Block context.
     * @param \Magento\Framework\Data\FormFactory                                     $formFactory Form factory.
     * @param \Magento\CatalogRule\Model\RuleFactory                                  $ruleFactory Rule Factory.
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator     Rule Locator.
     * @param array                                                                   $data        Additional data.
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->ruleFactory = $ruleFactory;
        $this->locator     = $locator;

        parent::__construct($context, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        return $this->escapeJsQuote($this->getForm()->toHtml());
    }

    /**
     * Get Rule
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface
     */
    private function getRule()
    {
        return $this->locator->getRule();
    }

    /**
     * Create the form containing the rule field.
     *
     * @return \Magento\Framework\Data\Form
     */
    private function getForm()
    {
        $rule = $this->ruleFactory->create();

        if ($this->getRule() && $this->getRule()->getCondition()) {
            $rule = $this->getRule()->getCondition();
        }

        $form = $this->formFactory->create();
        $form->setHtmlId('condition');

        $ruleConditionField = $form->addField(
            'condition',
            'text',
            ['name' => 'condition', 'label' => __('Rule conditions'), 'container_id' => 'condition']
        );

        $ruleConditionField->setValue($rule);
        $ruleConditionRenderer = $this->getLayout()
            ->createBlock('Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Rule\Renderer\Condition');
        $ruleConditionField->setRenderer($ruleConditionRenderer);

        return $form;
    }
}
