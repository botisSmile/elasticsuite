<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Campaign\Form\Modifier;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Campaign Ui Component Modifier. Used to populate search queries dynamicRows.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class SearchTerms implements ModifierInterface
{
    /**
     * @var CampaignContext
     */
    private $campaignContext;

    /**
     * @var CollectionFactory
     */
    private $queryCollection;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * Search Terms constructor.
     *
     * @param CampaignContext   $campaignContext        Campaign Context
     * @param CollectionFactory $queryCollectionFactory Search Collection Factory
     * @param Yesno             $yesNo                  Yes/No source value.
     */
    public function __construct(
        CampaignContext $campaignContext,
        CollectionFactory $queryCollectionFactory,
        Yesno $yesNo
    ) {
        $this->campaignContext = $campaignContext;
        $this->yesNo           = $yesNo;
        $this->queryCollection = $queryCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $campaign = $this->campaignContext->getCurrentCampaign();

        if ($campaign && $campaign->getId() && isset($data[$campaign->getId()])) {
            if (isset($data[$campaign->getId()]['quick_search_container'])
                && isset($data[$campaign->getId()]['quick_search_container']['query_ids'])) {
                $queriesData = $this->fillQueryData($data[$campaign->getId()]['quick_search_container']['query_ids']);
                $data[$campaign->getId()]['quick_search_container']['query_ids'] = [];
                $data[$campaign->getId()]['quick_search_container']['apply_to'] = (int) false;
                if (!empty($queriesData)) {
                    $data[$campaign->getId()]['quick_search_container']['query_ids'] = $queriesData;
                    $data[$campaign->getId()]['quick_search_container']['apply_to'] = (int) true;
                }
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get query data to fill the dynamicRows in Ui Component Form.
     *
     * @param integer[] $queryIds The query ids
     * @return array
     */
    private function fillQueryData($queryIds)
    {
        $data = [];

        $collection  = $this->queryCollection->addFieldToFilter('query_id', $queryIds);
        $yesNoValues = $this->yesNo->toArray();

        foreach ($collection as $query) {
            $data[] = [
                'id'              => $query->getId(),
                'query_text'      => $query->getQueryText(),
                'is_spellchecked' => $yesNoValues[(int) $query->getIsSpellchecked()],
            ];
        }

        return $data;
    }
}
