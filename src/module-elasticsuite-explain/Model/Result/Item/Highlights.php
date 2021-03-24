<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Item;

use Smile\ElasticsuiteCore\Api\Search\ContextInterface;
use Smile\ElasticsuiteExplain\Model\Thesaurus\Index;
use Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response\ExplainDocument;

/**
 * Highlights/Details builder for result item
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 */
class Highlights
{
    /**
     * Get document highlights : fields content with matched patterns.
     *
     * @param array            $docSource Document source
     * @param FieldInterface[] $fields    Index fields
     * @param array            $matches   Document matches
     *
     * @return array
     */
    public function getHighlights($docSource = [], $fields = [], $matches = [])
    {
        $results = $this->nameFields($docSource);

        foreach ($results as &$result) {
            $field = $fields[$result['sourceField']] ?? null;
            $result['is_searchable'] = false;
            if ($field && $field->isSearchable() && isset($result['value'])) {
                $result['is_searchable'] = true;
                $value = strip_tags(json_encode($result['value']));
                foreach ($matches as $query) {
                    $value = preg_replace('/\b'.$query.'\b/i', "<em>$0</em>", $value);
                }
                $result['value'] = json_decode($value);
            }
        }

        array_multisort(array_column($results, 'is_searchable'), SORT_DESC, $results);

        return array_values($results);
    }

    /**
     * Parse fields and rename them appropriately with a dotted notation :
     * field.subfield.subsubfield
     *
     * We also keep an unique numeric dotted versions for multiple subfields :
     * field.1.subfield.subsubfield
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     *
     * @param array $docSource Document source
     *
     * @return array
     */
    private function nameFields($docSource)
    {
        $results = [];

        foreach ($docSource as $fieldName => $value) {
            // Convert complex (array of objects) values to dot notation.
            if (is_array($value) &&
                ((count($value) != count($value, COUNT_RECURSIVE)) || (array_keys($value) !== range(0, count($value) - 1)))) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($value));

                foreach ($iterator as $leafValue) {
                    $keys = [];
                    foreach (range(0, $iterator->getDepth()) as $depth) {
                        $keys[] = $iterator->getSubIterator($depth)->key();
                    }
                    $dottedName = $fieldName . '.' . join('.', $keys);
                    $realName   = preg_replace('/\.[0-9]+\./i', ".", $dottedName);
                    $results[]  = ['field' => $dottedName, 'sourceField' => $realName, 'value' => $leafValue];
                }
            } else {
                $results[] = ['field' => $fieldName, 'sourceField' => $fieldName, 'value' => $value];
            }
        }

        return $results;
    }
}
