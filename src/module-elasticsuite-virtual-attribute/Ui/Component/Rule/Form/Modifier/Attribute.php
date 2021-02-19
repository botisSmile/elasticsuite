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
namespace Smile\ElasticsuiteVirtualAttribute\Ui\Component\Rule\Form\Modifier;

use Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface;

/**
 * Smile Elastic Suite Virtual Attribute rule edit form data provider modifier :
 *
 * Used to set "attribute_id" field to disabled in case of already existing rule.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Attribute implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface
     */
    private $locator;

    /**
     * AttributeOptions constructor.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator Rule Locator
     */
    public function __construct(
        \Smile\ElasticsuiteVirtualAttribute\Model\Rule\Locator\LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $rule = $this->locator->getRule();

        if ($rule && $rule->getId() && isset($data[$rule->getId()][RuleInterface::ATTRIBUTE_ID])) {
            $data[$rule->getId()]['attribute_label'] = $data[$rule->getId()][RuleInterface::ATTRIBUTE_ID];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $rule = $this->locator->getRule();

        $isNewRule = (!$rule || !$rule->getId());

        $meta['general']['children']['attribute_id']['arguments']['data']['config']['disabled'] = !$isNewRule;
        $meta['general']['children']['attribute_id']['arguments']['data']['config']['visible']  = $isNewRule;

        $meta['general']['children']['attribute_label']['arguments']['data']['config']['disabled'] = $isNewRule;
        $meta['general']['children']['attribute_label']['arguments']['data']['config']['visible']  = !$isNewRule;

        return $meta;
    }
}
