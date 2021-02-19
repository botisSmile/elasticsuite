<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Applier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Smile Elastic Suite Virtual Attribute Rules applier Table Strategy.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class TableStrategy
{
    /**
     * Retrieve temporary table that will be used for computing for a given attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute The attribute
     *
     * @return string
     */
    public function getTemporaryTableName(ProductAttributeInterface $attribute)
    {
        return 'elasticsuite_' . $attribute->getBackendTable() . '_tmp';
    }
}
