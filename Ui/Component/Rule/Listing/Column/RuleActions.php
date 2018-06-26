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
namespace Smile\ElasticsuiteVirtualAttribute\Ui\Component\Rule\Listing\Column;

/**
 * Action column for Smile Elastic Suite Virtual Attribute rules listing Ui component.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RuleActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Edit Url path
     **/
    const RULE_URL_PATH_EDIT = 'smile_elasticsuite_virtual_attribute/rule/edit';

    /**
     * Delete Url path
     **/
    const RULE_URL_PATH_DELETE = 'smile_elasticsuite_virtual_attribute/rule/delete';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context            Application context
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory Ui Component Factory
     * @param \Magento\Framework\UrlInterface                              $urlBuilder         URL Builder
     * @param array                                                        $components         Components
     * @param array                                                        $data               Component Data
     * @param string                                                       $editUrl            Edit Url
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::RULE_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl    = $editUrl;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource The data source
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if (isset($item['optimizer_id'])) {
                    $item[$name]['edit'] = [
                        'href'  => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['rule_id']]),
                        'label' => __('Edit'),
                    ];

                    $item[$name]['delete'] = [
                        'href'    => $this->urlBuilder->getUrl(self::RULE_URL_PATH_DELETE, ['id' => $item['rule_id']]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete rule'),
                            'message' => __('Are you sure you want to delete this rule ?'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
