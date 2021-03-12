<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Model\Thesaurus;

use Smile\ElasticsuiteCore\Api\Client\ClientInterface;
use Smile\ElasticsuiteCore\Helper\Cache as CacheHelper;
use Smile\ElasticsuiteCore\Helper\IndexSettings as IndexSettingsHelper;
use Smile\ElasticsuiteThesaurus\Api\Data\ThesaurusInterface;
use Smile\ElasticsuiteThesaurus\Config\ThesaurusConfigFactory;
use Smile\ElasticsuiteThesaurus\Model\Index as ThesaurusIndex;

/**
 * Thesaurus index.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Le Maguer <pilem@smile.fr>
 */
class Index extends ThesaurusIndex
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IndexSettingsHelper
     */
    private $indexSettingsHelper;

    /**
     * @var ThesaurusConfigFactory
     */
    private $thesaurusConfigFactory;

    /**
     * @var CacheHelper
     */
    private $cacheHelper;

    /**
     * Constructor.
     *
     * @param ClientInterface        $client                 ES client.
     * @param IndexSettingsHelper    $indexSettingsHelper    Index Settings Helper.
     * @param CacheHelper            $cacheHelper            ES caching helper.
     * @param ThesaurusConfigFactory $thesaurusConfigFactory Thesaurus configuration factory.
     */
    public function __construct(
        ClientInterface $client,
        IndexSettingsHelper $indexSettingsHelper,
        CacheHelper $cacheHelper,
        ThesaurusConfigFactory $thesaurusConfigFactory
    ) {
        $this->client                 = $client;
        $this->indexSettingsHelper    = $indexSettingsHelper;
        $this->thesaurusConfigFactory = $thesaurusConfigFactory;
        $this->cacheHelper            = $cacheHelper;
        parent::__construct($client, $indexSettingsHelper, $cacheHelper, $thesaurusConfigFactory);
    }

    /**
     * Generates all possible synonyms for a store and query text.
     *
     * @param integer $storeId   Store id.
     * @param string  $queryText Text query.
     * @param string  $type      Substitution type (synonym or expansion).
     *
     * @return array
     */
    public function getSynonyms(int $storeId, string $queryText, string $type = ThesaurusInterface::TYPE_SYNONYM): array
    {
        $indexName = $this->indexSettingsHelper->getIndexAliasFromIdentifier(self::INDEX_IDENTIER, $storeId);

        try {
            $analysis = $this->client->analyze(
                ['index' => $indexName, 'body' => ['text' => $queryText, 'analyzer' => $type]]
            );
        } catch (\Exception $e) {
            $analysis = ['tokens' => []];
        }

        $synonyms = [];

        foreach ($analysis['tokens'] ?? [] as $token) {
            if ($token['type'] == 'SYNONYM') {
                $token['token'] = str_replace('_', ' ', $token['token']);
                $synonyms[] = $token;
            }
        }

        return $synonyms;
    }
}
