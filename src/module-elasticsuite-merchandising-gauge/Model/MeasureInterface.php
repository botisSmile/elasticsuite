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
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
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
