<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommenderGraphQl
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommenderGraphQl\Model\Resolver\VisitorProducts;

use GraphQL\Language\AST\SelectionNode;
use Magento\Framework\GraphQl\Query\FieldTranslator;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Field Selector for Visitor Products
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommenderGraphQl
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class FieldSelector
{
    /**
     * @var FieldTranslator
     */
    private $fieldTranslator;

    /**
     * @param FieldTranslator $fieldTranslator Field Translator
     */
    public function __construct(FieldTranslator $fieldTranslator)
    {
        $this->fieldTranslator = $fieldTranslator;
    }

    /**
     * Get requested fields from products query
     *
     * @param ResolveInfo $resolveInfo Resolve Info
     *
     * @return string[]
     */
    public function getProductsFieldSelection(ResolveInfo $resolveInfo): array
    {
        return $this->getProductFields($resolveInfo);
    }

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info Resolve Info
     *
     * @return string[]
     */
    private function getProductFields(ResolveInfo $info): array
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            foreach ($node->selectionSet->selections as $selection) {
                if ($selection->name->value !== 'items') {
                    continue;
                }
                $fieldNames[] = $this->collectProductFieldNames($selection, $fieldNames);
            }
        }
        if (!empty($fieldNames)) {
            $fieldNames = array_merge(...$fieldNames);
        }

        return $fieldNames;
    }

    /**
     * Collect field names for each node in selection
     *
     * @param SelectionNode $selection  Node
     * @param array         $fieldNames Field names
     *
     * @return array
     */
    private function collectProductFieldNames(SelectionNode $selection, array $fieldNames = []): array
    {
        foreach ($selection->selectionSet->selections as $itemSelection) {
            if ($itemSelection->kind === 'InlineFragment') {
                foreach ($itemSelection->selectionSet->selections as $inlineSelection) {
                    if ($inlineSelection->kind === 'InlineFragment') {
                        continue;
                    }
                    $fieldNames[] = $this->fieldTranslator->translate($inlineSelection->name->value);
                }
                continue;
            }
            $fieldNames[] = $this->fieldTranslator->translate($itemSelection->name->value);
        }

        return $fieldNames;
    }
}
