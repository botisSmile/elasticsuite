<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Result\Item;

use Smile\ElasticsuiteCore\Api\Index\MappingInterface;

/**
 * Iterates over an array of field matches and interactively builds a list of description
 * to explain the role and nature of matching fields.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class DescriptionsGenerator
{
    /**
     * @var array
     */
    private $defaultFields;

    /**
     * @var array
     */
    private $analyzers;

    /**
     * DescriptionsGenerator constructor.
     *
     * @param array $defaultFields Default fields descriptions.
     * @param array $analyzers     Analyzers descriptions.
     */
    public function __construct(
        $defaultFields = [],
        $analyzers = []
    ) {
        $this->defaultFields = $defaultFields;
        $this->analyzers     = $analyzers;
    }

    /**
     * Iterate over the field matches and build an array of field descriptions.
     *
     * @param array $fieldMatches Field matches.
     *
     * @return string[]
     */
    public function generate($fieldMatches = [])
    {
        $descriptions = [];

        $defaultFields = [
            MappingInterface::DEFAULT_SEARCH_FIELD,
            MappingInterface::DEFAULT_SPELLING_FIELD,
            MappingInterface::DEFAULT_AUTOCOMPLETE_FIELD,
        ];
        foreach ($fieldMatches as $fieldMatch) {
            $field = $fieldMatch['field'];
            if (in_array($field, $defaultFields)) {
                $this->addDefaultFieldDescription($field, $descriptions);
            } // Else: attribute.

            if ($analyzer = $fieldMatch['analyzer']) {
                $this->addAnalyzerDescription($analyzer, $descriptions);
            }
        }

        ksort($descriptions);

        return $descriptions;
    }

    /**
     * Add the description for a default field to the list of descriptions.
     *
     * @param string $defaultField Default field.
     * @param array  $descriptions Current list of descriptions.
     *
     * @return void
     */
    private function addDefaultFieldDescription($defaultField, &$descriptions)
    {
        if (!array_key_exists($defaultField, $descriptions)) {
            if ($fieldDescription = $this->getFieldDescription($defaultField)) {
                $descriptions[$defaultField] = [
                    'field'     => $defaultField,
                    'legend'    => $fieldDescription,
                ];
            }
        }
    }

    /**
     * Add the description for an analyzer the list of descriptions.
     *
     * @param string $analyzer     Analyzer.
     * @param array  $descriptions Current list of descriptions.
     *
     * @return void
     */
    private function addAnalyzerDescription($analyzer, &$descriptions)
    {
        if (!array_key_exists($analyzer, $descriptions)) {
            if ($analyzerDescription = $this->getAnalyzerDescription($analyzer)) {
                $descriptions[$analyzer] = [
                    'field'     => "*.{$analyzer}",
                    'legend'    => $analyzerDescription,
                ];
            }
        }
    }

    /**
     * Get analyzer description, if available.
     *
     * @param string $analyzer Analyzer.
     *
     * @return string|null
     */
    private function getAnalyzerDescription($analyzer)
    {
        $description = null;

        if (array_key_exists($analyzer, $this->analyzers)) {
            $description = __($this->analyzers[$analyzer]);
        }

        return $description;
    }

    /**
     * Get field description, if available.
     *
     * @param string $field Field.
     *
     * @return string|null
     */
    private function getFieldDescription($field)
    {
        $description = null;

        if (array_key_exists($field, $this->defaultFields)) {
            $description = __($this->defaultFields[$field]);
        }

        return $description;
    }
}
