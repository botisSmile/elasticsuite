<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model\Search\Request\Product\Source;

use Smile\ElasticsuiteCatalogOptimizer\Model\Search\Request\Product\Source\Containers as OptimizerContainers;
use Smile\ElasticsuiteCore\Search\Request\ContainerConfiguration\BaseConfig;

/**
 * Source model for search request containers related to products only for campaigns.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Containers extends OptimizerContainers
{
    /**
     * @var array
     */
    private $allowedContainers = [];

    /**
     * Containers constructor
     *
     * @param BaseConfig $baseConfig        Base config
     * @param array      $allowedContainers Allowed containers
     */
    public function __construct(BaseConfig $baseConfig, array $allowedContainers = [])
    {
        parent::__construct($baseConfig);
        $this->allowedContainers = $allowedContainers;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $optimizerContainers = parent::toOptionArray();

        return array_filter($optimizerContainers, function ($value) {
            return in_array($value['value'], $this->allowedContainers);
        });
    }
}
