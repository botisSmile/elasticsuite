<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralData
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBehavioralData\Search\Request\Aggregation\Bucket;

use Smile\ElasticsuiteCore\Search\Request\Aggregation\Bucket\AbstractBucket;

/**
 * Reverse nested aggregation implementation.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralData
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ReverseNested extends AbstractBucket
{
    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return 'reverseNestedBucket';
    }
}
