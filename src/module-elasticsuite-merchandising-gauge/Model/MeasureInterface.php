<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandisingGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Model;

/**
 * Interface MeasureInterface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
interface MeasureInterface
{
    /**
     * Return gauge data
     *
     * @return array
     */
    public function getData();
}
