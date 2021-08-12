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

namespace Smile\ElasticsuiteExplain\Model\Result;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Customer\Api\Data\GroupInterface;
use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\FunctionScore;
use Smile\ElasticsuiteExplain\Model\Result\Item\DescriptionsGenerator;
use Smile\ElasticsuiteExplain\Model\Result\Item\Highlights;
use Smile\ElasticsuiteExplain\Model\Result\Item\SynonymManager;
use Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response\ExplainDocument;

/**
 * Result Item Model for Explain
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Item
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    private $document;

    /**
     * @var FieldInterface[]
     */
    private $fields;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var null|array
     */
    private $matches = null;

    /**
     * @var SynonymManager
     */
    private $synonymManager;

    /**
     * @var Highlights
     */
    private $highlights;

    /**
     * @var DescriptionsGenerator
     */
    private $descriptionsGenerator;

    /**
     * Constructor.
     *
     * @param ProductInterface      $product               Product.
     * @param ExplainDocument       $document              Product Document.
     * @param ImageHelper           $imageHelper           Image helper.
     * @param SynonymManager        $synonymManager        Synonym Manager.
     * @param DescriptionsGenerator $descriptionsGenerator Descriptions generator.
     * @param Highlights            $highlights            Highlights retriever.
     * @param FieldInterface[]      $fields                All fields of the mapping.
     */
    public function __construct(
        ProductInterface $product,
        ExplainDocument $document,
        ImageHelper $imageHelper,
        SynonymManager $synonymManager,
        DescriptionsGenerator $descriptionsGenerator,
        Highlights $highlights,
        array $fields = []
    ) {
        $this->product        = $product;
        $this->document       = $document;
        $this->imageHelper    = $imageHelper;
        $this->synonymManager = $synonymManager;
        $this->descriptionsGenerator = $descriptionsGenerator;
        $this->highlights     = $highlights;
        $this->fields         = $fields;
    }

    /**
     * Item data.
     *
     * @return array
     */
    public function getData()
    {
        $data = [
            'id'              => (int) $this->document->getId(),
            'name'            => $this->getDocumentSource('name'),
            'sku'             => $this->getDocumentSource('sku'),
            'price'           => $this->getProductPrice(),
            'image'           => $this->getProductImage(),
            'score'           => $this->getDocumentScore(),
            'explanation'     => $this->getDocumentExplanation(),
            'sort'            => $this->getDocumentSort(),
            'is_in_stock'     => $this->isInStockProduct(),
            'boosts'          => $this->getBoosts(),
            'matches'         => $this->getMatches(),
            'highlights'      => $this->getHighlights(),
            'legends'         => $this->getLegends(),
            'behavioral_data' => $this->getBehavioralData(),
        ];

        return $data;
    }

    /**
     * Return the ES explanation document for the current product.
     *
     * @return array
     */
    private function getDocumentExplanation()
    {
        return $this->document->getExplain() ? : [];
    }

    /**
     * Return the ES source document for the current product.
     *
     * @param string $field The document field to retrieve.
     *
     * @return array
     */
    private function getDocumentSource($field = null)
    {
        $docSource = $this->document->getSource() ? : [];
        $result    = $docSource;

        if (null !== $field) {
            $result = null;
            if (isset($docSource[$field])) {
                $result = is_array($docSource[$field]) ? current($docSource[$field]) : $docSource[$field];
            }
        }

        return $result;
    }

    /**
     * Return the ES score for the current product.
     *
     * @return float
     */
    private function getDocumentScore()
    {
        return $this->document->getScore();
    }

    /**
     * Return the ES sort values for the current product.
     *
     * @return array
     */
    private function getDocumentSort()
    {
        return $this->document->getSort() ?: [];
    }

    /**
     * Retrieve product small image
     *
     * @return string
     */
    private function getProductImage()
    {
        $image = $this->getDocumentSource('image');
        if ($image) {
            $this->product->setSmallImage($image);
        }

        $this->imageHelper->init($this->product, 'smile_elasticsuite_product_sorter_image');

        return $this->imageHelper->getUrl();
    }

    /**
     * Returns current product sale price.
     *
     * @return float
     */
    private function getProductPrice()
    {
        $price    = 0;
        $document = $this->getDocumentSource();

        if (isset($document['price'])) {
            foreach ($document['price'] as $currentPrice) {
                if ((int) $currentPrice['customer_group_id'] === GroupInterface::NOT_LOGGED_IN_ID) {
                    $price = (float) $currentPrice['price'];
                }
            }
        }

        return $price;
    }

    /**
     * Returns current product stock status.
     *
     * @return bool
     */
    private function isInStockProduct()
    {
        $isInStock = false;
        $document = $this->getDocumentSource();
        if (isset($document['stock']['is_in_stock'])) {
            $isInStock = (bool) $document['stock']['is_in_stock'];
        }

        return $isInStock;
    }

    /**
     * Return boosts applied to the product.
     *
     * @return array
     */
    private function getBoosts()
    {
        $boosts = [];

        if ($explain = $this->getDocumentExplanation()) {
            $boosts = $this->getBoostsFromExplain($explain);
        }

        return $boosts;
    }

    /**
     * Get boost weights from explain.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param array $explain Explain data
     *
     * @return array
     */
    private function getBoostsFromExplain(array $explain)
    {
        $boosts = [];

        if (array_key_exists('description', $explain)) {
            $description = $explain['description'];

            // On a function score node, extract boost/weight.
            if (preg_match('/^function score, score mode/', $description)) {
                $boosts['boost_mode'] = FunctionScore::BOOST_MODE_MULTIPLY;
                if (array_key_exists('value', $explain)) {
                    $boosts['weight'] = $explain['value'];
                }
                if (array_key_exists('details', $explain) && is_array($explain['details'])) {
                    $boosts['total'] = count($explain['details'] ?? []);
                    if (!empty($explain['details'])) {
                        $boosts['details'] = $explain['details'];
                    }
                }
                if (preg_match('/\\[(.*?)\\]/', $description, $scoreMode)) {
                    $boosts['score_mode'] = next($scoreMode);
                }
            } elseif (array_key_exists('details', $explain)) {
                $details = $explain['details'];
                if (is_array($details)) {
                    foreach ($details as $child) {
                        $boosts = array_replace_recursive($boosts, $this->getBoostsFromExplain($child));
                    }
                }
            }
        }

        return $boosts;
    }


    /**
     * Get field/text matches from the explanation data, if available.
     *
     * @return array
     */
    private function getMatches()
    {
        if (null === $this->matches) {
            $matches = [];

            if ($explain = $this->getDocumentExplanation()) {
                $matches = $this->getFieldMatches($explain);
            }

            $this->matches = $matches;
        }

        return $this->matches;
    }

    /**
     * Collect all field matches scores.
     *
     * @param array $explain Explain data.
     *
     * @return array
     */
    private function getFieldMatches(array $explain)
    {
        $fieldMatches = [];
        if (array_key_exists('description', $explain)) {
            $description = $explain['description'];

            $matches = [];
            /**
             * Example of descriptions that we want to retrieve:
             *   - weight(search:atom in 618) [PerFieldSimilarity], result of:
             *   - weight(Synonym(search.shingle:blue search.shingle:blue jacket) in 1318)
             *       [PerFieldSimilarity], result of:
             *   - weight(Synonym(name.shingle:atomic name.shingle:atomic endurance
             *       name.shingle:atomic endurance running) in 618) [PerFieldSimilarity], result of:
             */
            if (preg_match(
                '/^weight\((?:.*\()?(?:([^:]+):([^\)]*))\)? in/',
                $description,
                $matches
            )) {
                $field = $matches[1]; // We retrieve the field, like 'name.shingle'.
                $termsMatch = $matches[2]; // We retrieve the part with terms, like 'blue search.shingle:blue jacket'.
                $terms = explode("$field:", $termsMatch); // We have here an array like ['blue ', 'blue jacket'].
                $terms = array_map("trim", $terms); // We remove unwanted spaces, ['blue', 'blue jacket'].
                $query = implode(', ', $terms);
                $score = $explain['value'] ?? 0;
                $weight = 1;
                if (array_key_exists('details', $explain)) {
                    if (is_array($explain['details'])) {
                        $weight = $this->getFieldBoost($explain['details']);
                    }
                }

                $analyzer = false;
                if (preg_match('/^([^.]+)\.(.*)$/', $field, $analyzerMatches)) {
                    $field      = $analyzerMatches[1];
                    $analyzer   = $analyzerMatches[2];
                }

                $fieldMatches[] = [
                    'field'     => $field,
                    'analyzer'  => $analyzer,
                    'query'     => $query,
                    'weight'    => $weight,
                    'score'     => $score,
                    'synonym'   => $this->synonymManager->getSynonym($query),
                ];
            } elseif (array_key_exists('details', $explain)) {
                $details = $explain['details'];
                if (is_array($details)) {
                    foreach ($details as $child) {
                        $fieldMatches = array_merge($fieldMatches, $this->getFieldMatches($child));
                    }
                }
            }
        }

        return $fieldMatches;
    }

    /**
     * Look for a possible score boost node in the explain data.
     *
     * @param array $explain Explain data.
     *
     * @return float
     */
    private function getFieldBoost(array $explain)
    {
        $boost = 1;

        foreach ($explain as $child) {
            if (array_key_exists('description', $child)) {
                $description = $child['description'];
                if ($description === 'boost') {
                    $boost = $child['value'];
                    break;
                }
            }

            if (array_key_exists('details', $child)) {
                $details = $child['details'];
                if (is_array($details)) {
                    $boost = $this->getFieldBoost($details);
                }
            }
        }

        return $boost;
    }

    /**
     * Get document highlights : fields content with matched patterns.
     *
     * @return array
     */
    private function getHighlights()
    {
        $matchedQueries = [];
        foreach ($this->getMatches() as $match) {
            if (isset($match['query']) && ($match['query'] !== '')) {
                $matchedQueries[] = $match['query'];
            }
        }
        $matchedQueries = array_unique($matchedQueries);

        return $this->highlights->getHighlights($this->getDocumentSource(), $this->fields, $matchedQueries);
    }

    /**
     * Get fields and analyzers related legends.
     *
     * @return string[]
     */
    private function getLegends()
    {
        return $this->descriptionsGenerator->generate($this->getMatches());
    }

    /**
     * Get behavioral data for the current item.
     *
     * @return array
     */
    private function getBehavioralData()
    {
        return $this->document->getSource()['_stats'] ?? [];
    }
}
