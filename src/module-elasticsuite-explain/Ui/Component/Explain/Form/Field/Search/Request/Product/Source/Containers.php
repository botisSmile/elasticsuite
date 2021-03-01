<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Ui\Component\Explain\Form\Field\Search\Request\Product\Source;

use Smile\ElasticsuiteCore\Search\Request\ContainerConfiguration\BaseConfig;

/**
 * Data provider for adminhtml explain form
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Containers extends \Smile\ElasticsuiteCore\Model\Search\Request\Source\Containers
{
    /**
     * @var array A list of search containers usable for Explain.
     */
    private $whitelist;

    /**
     * Containers constructor.
     *
     * @param \Smile\ElasticsuiteCore\Search\Request\ContainerConfiguration\BaseConfig $baseConfig Base configuration
     * @param array                                                                    $whitelist  The authorized containers
     */
    public function __construct(BaseConfig $baseConfig, $whitelist = [])
    {
        parent::__construct($baseConfig);

        $this->whitelist = $whitelist;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getContainers() as $container) {
            if (in_array($container['name'] ?? '', $this->whitelist)) {
                $options[] = [
                    'value'    => $container['name'],
                    'label'    => __($container['label']),
                    'fulltext' => isset($container['fulltext']) && $container['fulltext'] == "true" ? true : false,
                ];
            }
        }

        return $options;
    }
}
