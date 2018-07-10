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
namespace Smile\ElasticsuiteVirtualAttribute\Block\Adminhtml\Rule\Renderer;

/**
 * Rule field renderer.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Condition
    extends \Magento\Backend\Block\Template
    implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\CatalogRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $element;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $input;

    /**
     * @var string
     */
    protected $_template = 'Smile_ElasticsuiteCatalogRule::product/conditions.phtml';

    /**
     * Block constructor.
     *
     * @param \Magento\Backend\Block\Template\Context      $context        Templating context.
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory Form element factory.
     * @param \Magento\Rule\Block\Conditions               $conditions     Rule conditions block.
     * @param \Magento\CatalogRule\Model\RuleFactory       $ruleFactory    Catalog rule factory.
     * @param array                                        $data           Additional data.
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->conditions     = $conditions;
        $this->rule           = $ruleFactory->create();

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;

        return $this->toHtml();
    }

    /**
     * Get URL used to create a new child condition into the rule.
     *
     * @return string
     */
    public function getNewChildUrl()
    {
        $urlParams = [
            'form'         => $this->getElement()->getContainer()->getHtmlId(),
            'element_name' => $this->getElement()->getName(),
        ];

        return $this->getUrl('smile_elasticsuite_virtual_attribute/rule/conditions', $urlParams);
    }

    /**
     * Get currently edited element.
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Retrieve element unique container id.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getElement()->getContainer()->getHtmlId();
    }

    /**
     * Render HTML of the element using the rule engine.
     *
     * @return string
     */
    public function getInputHtml()
    {
        $this->rule->setElementName($this->element->getName());

        if ($this->element->getValue()) {
            /* Hack : reload in a new instance to have element name set.
             *        can not be done in afterLoad of the backend model
             *        since we do not know yet the form structure
             */
            $conditions = $this->element->getValue();
            if (!is_array($conditions)) {
                $conditions = $conditions->getConditions();
            }
            $this->rule->getConditions()->loadArray($conditions);
            $this->element->setRule($this->rule);
        }

        $this->input = $this->elementFactory->create('text');
        $this->input->setRule($this->rule)->setRenderer($this->conditions);
        $this->setConditionFormName($this->rule->getConditions(), $this->getElement()->getContainer()->getHtmlId());

        return $this->input->toHtml();
    }

    /**
     * Set proper form name to rule conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions Rule conditions.
     * @param string                                          $formName   Form Name.
     *
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setJsFormObject($formName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
