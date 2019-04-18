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
     * Maximum number of products to display for related products configuration path
     * @var string
     */
    const CONFIG_RELATED_POSITION_LIMIT_XPATH = 'smile_elasticsuite_recommender/general/related_position_limit';

    /**
     * Maximum number of products to display for related products configuration path
     * @var string
     */
    const CONFIG_RELATED_EXCLUDE_COMPOSITE_XPATH = 'smile_elasticsuite_recommender/general/related_exclude_composite_products';

    /**
     * Maximum number of products to display for cross-sell products configuration path
     * @var string
     */
    const CONFIG_CROSSSELL_POSITION_LIMIT_XPATH = 'smile_elasticsuite_recommender/general/crosssell_position_limit';

    /**
     * Maximum number of products to display for upsell products configuration path
     * @var string
     */
    const CONFIG_UPSELL_POSITION_LIMIT_XPATH = 'smile_elasticsuite_recommender/general/upsell_position_limit';

    /**
     * Force higher price for upsells flag configuration path
     * @var string
     */
    const CONFIG_UPSELL_FORCE_HIGHER_PRICE_XPATH = 'smile_elasticsuite_recommender/general/upsell_force_higher_price';

    /**
     * Whether to exclude products already bought by the visitor configuration path
     * @var string
     */
    const CONFIG_EXCLUDE_ALREADY_BOUGHT_XPATH = 'smile_elasticsuite_recommender/general/exclude_already_bought';

    /**
     * Absolute maximum number of products to display for related/upsell/cross-sell products.
     * @var int
     */
    const MAX_POSITION_LIMIT = 20;

    /**
     * Block type to product list type mappings
     * @var array
     */
    const BLOCK_TO_PRODUCT_LIST_TYPES = [
        'related'           => 'related',
        'related-rule'      => 'related',
        'crosssell'         => 'crosssell',
        'crosssell-rule'    => 'crosssell',
        'upsell'            => 'upsell',
        'upsell-rule'       => 'upsell',
    ];

    /**
     * Returns the mininum number of documents/sessions an event must be present in to be relevant
     *
     * @return int
     */
    public function getCoOccurrenceMinDocCount()
    {
        return (int) $this->scopeConfig->getValue(self::CONFIG_COOCCURRENCE_MIN_DOC_COUNT_XPATH);
    }

    /**
     * Returns the maximum number of products to display for related products
     *
     * @param string $blockType Block type.
     *
     * @return int
     */
    public function getPositionLimit($blockType)
    {
        $prefix = $this->getTypePrefix($blockType);

        $positionLimit = (int) $this->scopeConfig->getValue(
            sprintf('smile_elasticsuite_recommender/general/%s_position_limit', $prefix)
        );
        if ($positionLimit == 0 || ($positionLimit > self::MAX_POSITION_LIMIT)) {
            $positionLimit = self::MAX_POSITION_LIMIT;
        }

        return $positionLimit;
    }

    /**
     * Return the configured behavior for showing products
     *
     * @param string $blockType Block type.
     *
     * @return int
     */
    public function getBehavior($blockType)
    {
        $prefix = $this->getTypePrefix($blockType);

        $behavior = (int) $this->scopeConfig->getValue(
            sprintf('smile_elasticsuite_recommender/general/%s_behavior', $prefix)
        );

        return $behavior;
    }

    /**
     * Returns true if products of a composite product type should be excluded from automated related products
     *
     * @return bool
     */
    public function isExcludingCompositeForRelated()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_RELATED_EXCLUDE_COMPOSITE_XPATH);
    }

    /**
     * Returns true if automated upsells products must have a higher price then the base product
     *
     * @return bool
     */
    public function isForceHigherPriceForUpsells()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_UPSELL_FORCE_HIGHER_PRICE_XPATH);
    }

    /**
     * Returns true if products already bought in the past by the user should be excluded from recommendations.
     *
     * @return bool
     */
    public function isExcludingPastBoughtProducts()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_EXCLUDE_ALREADY_BOUGHT_XPATH);
    }

    /**
     * Return the config xml path prefix of a given block type
     *
     * @param string $blockType Block type
     *
     * @return string
     */
    private function getTypePrefix($blockType)
    {
        $type = $this->getProductListType($blockType);

        return $type;
    }

    /**
     * Returns the rationalized product list type (up-sell, related or cross-sell) according to the block type.
     *
     * @param string $blockType Provided block type.
     *
     * @return string
     */
    private function getProductListType($blockType = 'related')
    {
        $type = 'related';

        if (array_key_exists($blockType, self::BLOCK_TO_PRODUCT_LIST_TYPES)) {
            $type = self::BLOCK_TO_PRODUCT_LIST_TYPES[$blockType];
        }

        return $type;
    }
}
