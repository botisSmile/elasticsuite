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

namespace Smile\ElasticsuiteMerchandisingGauge\Plugin\Optimizer\Preview;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Preview\Item;

/**
 * Optimizer preview item plugin.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandisingGauge
 */
class ItemPlugin
{
    /**
     * After plugin - adds behavioral statistics about the product to the item data.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Item  $subject         Optimizer preview item.
     * @param array $productItemData Product item data.
     *
     * @return array
     */
    public function afterGetData(
        Item $subject,
        $productItemData
    ) {
        // TODO ribay@smile.fr : decide what to do (no access to product or document source)

        return $productItemData;
    }
}
