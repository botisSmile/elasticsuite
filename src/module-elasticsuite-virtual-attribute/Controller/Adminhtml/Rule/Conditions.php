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
namespace Smile\ElasticsuiteVirtualAttribute\Controller\Adminhtml\Rule;

/**
 * Smile Elastic Suite Virtual Attribute rule conditions controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Conditions extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Smile_ElasticsuiteVirtualAttribute::manage';

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\App\Action\Context    $context     Context.
     * @param \Magento\CatalogRule\Model\RuleFactory $ruleFactory Search engine rule factory.
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $conditionId = $this->getRequest()->getParam('id');
        $typeData    = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $className   = $typeData[0];

        $rule = $this->ruleFactory->create();

        $model = $this->_objectManager->create($className)
            ->setId($conditionId)
            ->setType($className)
            ->setRule($rule)
            ->setPrefix('conditions');

        $model->setElementName($this->getRequest()->getParam('element_name'));

        if (!empty($typeData[1])) {
            $model->setAttribute($typeData[1]);
        }

        $result = '';
        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $result = $model->asHtmlRecursive();
        }

        $this->getResponse()->setBody($result);
    }
}
