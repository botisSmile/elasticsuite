<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher\Source;

/**
 * Product matcher behavior source model
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Behavior implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get data for behavior selector
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \Smile\ElasticsuiteRecommender\Model\Product\Matcher::BOTH_SELECTED_AND_EVENT_BASED => __('Both Selected and Event-Based'),
            \Smile\ElasticsuiteRecommender\Model\Product\Matcher::SELECTED_ONLY => __('Selected Only'),
            \Smile\ElasticsuiteRecommender\Model\Product\Matcher::EVENT_BASED_ONLY => __('Event-Based Only'),
        ];
    }
}
