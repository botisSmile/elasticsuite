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
 * Used to add the proper value for reloadUrl inside UI component configuration.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class System implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * Search Terms constructor.
     *
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder URL Builder
     */
    public function __construct(\Magento\Backend\Model\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $reloadParameters = [
            'popup'         => 1,
            'componentJson' => 1,
        ];

        $data['config']['reloadUrl'] = $this->urlBuilder->getUrl('*/*/reload', $reloadParameters);

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
