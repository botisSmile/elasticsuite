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

/**
 * Smile Elastic Suite Virtual Attribute rule edit form data provider modifier :
 *
 * Used to populate "store_id" field according to current value of "store_id" for current rule.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Stores implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
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

        if ($rule && $rule->getId() && !empty($rule->getStores())) {
            $data[$rule->getId()]['store_id'] = $rule->getStores();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
