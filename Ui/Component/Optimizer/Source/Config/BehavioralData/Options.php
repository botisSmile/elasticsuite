<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralOptimizer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteBehavioralOptimizer\Ui\Component\Optimizer\Source\Config\BehavioralData;

/**
 * Attributes Options Values for behavioral optimizers.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var \Smile\ElasticsuiteBehavioralData\Model\Config
     */
    private $config;

    /**
     * Options constructor.
     *
     * @param \Smile\ElasticsuiteBehavioralData\Model\Config $config Configuration
     * @param array                                          $fields Fields
     */
    public function __construct(
        \Smile\ElasticsuiteBehavioralData\Model\Config $config,
        $fields = []
    ) {
        $this->config = $config;
        $this->fields = array_merge_recursive($this->getDefaultFields(), $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->fields as $field => $label) {
            if (!$this->config->isUseWeeklyStats() && strpos($field, '.weekly.')) {
                continue;
            }

            $options[$field] = [
                'value' => $field,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    private function getDefaultFields()
    {
        return [
            '_stats.views.total'        => __('Views (total)'),
            '_stats.views.daily.ma'     => __('Daily views (average)'),
            '_stats.views.daily.count'  => __('Daily views (number)'),
            '_stats.views.weekly.ma'    => __('Weekly views (average)'),
            '_stats.views.weekly.count' => __('Weekly views (number)'),
            '_stats.sales.total'        => __('Sales (total)'),
            '_stats.sales.daily.ma'     => __('Daily sales (average)'),
            '_stats.sales.daily.count'  => __('Daily sales (number)'),
            '_stats.sales.weekly.ma'    => __('Weekly sales (average)'),
            '_stats.sales.weekly.count' => __('Weekly sales (number)'),
        ];
    }
}
