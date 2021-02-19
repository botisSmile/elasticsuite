<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Matcher;

use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterface;
use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldFilterInterface;

/**
 * Used to filter fields technically valid for being used in a "more like this" query.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 * @author   Richard BAYET <richard.bayet@smile.fr>
 */
class SimilarityAbleFieldFilter implements FieldFilterInterface
{
    /**
     * {@inheritDoc}
     */
    public function filterField(FieldInterface $field)
    {
        $isTextField = $field->getType() == FieldInterface::FIELD_TYPE_TEXT;

        return ($isTextField && $field->isSearchable() && ($field->isUsedForSortBy() || $field->isFilterable()));
    }
}
