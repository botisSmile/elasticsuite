<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAnalytics
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Helper;

/**
 * Data helper.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Minimum number of documents/sessions an event must be present in to be relevant configuration path
     * @var string
     */
    const CONFIG_COOCCURRENCE_MIN_DOC_COUNT_XPATH = 'smile_elasticsuite_recommender/cooccurrence/min_doc_count';

    /**
     * Returns the mininum number of documents/sessions an event must be present in to be relevant
     *
     * @return int
     */
    public function getCoOccurrenceMinDocCount()
    {
        return (int) $this->scopeConfig->getValue(self::CONFIG_COOCCURRENCE_MIN_DOC_COUNT_XPATH);
    }
}
