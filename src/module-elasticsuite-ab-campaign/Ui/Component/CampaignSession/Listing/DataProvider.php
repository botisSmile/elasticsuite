<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\CampaignSession\Listing;

use Magento\Ui\DataProvider\AbstractDataProvider as BaseDataProvider;
use Smile\ElasticsuiteAbCampaign\Model\CampaignSession;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession\Collection as CampaignSessionCollection;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession\CollectionFactory;
use Smile\ElasticsuiteAbCampaign\Helper\Data as AbCampaignHelper;

/**
 * Optimizer listing data provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class DataProvider extends BaseDataProvider
{

    /**
     * @var CampaignSessionCollectionProcessorInterface[]
     */
    protected $collectionProcessors;

    /**
     * @var AbCampaignHelper
     */
    protected $abCampaignHelper;

    /**
     * DataProvider Constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param string                                        $name                 Component name
     * @param string                                        $primaryFieldName     Primary field Name
     * @param string                                        $requestFieldName     Request field name
     * @param CollectionFactory                             $collectionFactory    The collection factory
     * @param AbCampaignHelper                              $abCampaignHelper     Ab campaign helper
     * @param CampaignSessionCollectionProcessorInterface[] $collectionProcessors Collection processors
     * @param array                                         $meta                 Component Meta
     * @param array                                         $data                 Component extra data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        AbCampaignHelper $abCampaignHelper,
        array $collectionProcessors = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->collectionProcessors = $collectionProcessors;
        $this->abCampaignHelper = $abCampaignHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        /** @var CampaignSessionCollection $collection */
        $collection = $this->getCollection();

        // Apply processors to the optimizer collection.
        if (!$collection->isLoaded()) {
            $collection->addFieldToSelect('*');
            foreach ($this->collectionProcessors as $collectionProcessor) {
                $collectionProcessor->process($collection);
            }
        }

        $itemsData = [];
        /** @var CampaignSession $campaignSession */
        foreach ($collection->getItems() as $campaignSession) {
            $itemsData[] = array_merge(
                $campaignSession->toArray(),
                [
                    'session_count_a' => $this->abCampaignHelper->formatCampaignSessionValue(
                        $campaignSession->getSessionCountTotal(),
                        $campaignSession->getSessionCountA()
                    ),
                    'session_count_b' => $this->abCampaignHelper->formatCampaignSessionValue(
                        $campaignSession->getSessionCountTotal(),
                        $campaignSession->getSessionCountB()
                    ),
                    'conversion_rate_a' => $this->abCampaignHelper->formatInPercentage(
                        $campaignSession->getConversionRateA()
                    ),
                    'conversion_rate_b' => $this->abCampaignHelper->formatInPercentage(
                        $campaignSession->getConversionRateB()
                    ),
                    'significance' => $this->abCampaignHelper->renderSignificance(
                        $campaignSession->isSignificance()
                    ),
                ]
            );
        }

        return ['totalRecords' => count($itemsData), 'items' => $itemsData];
    }
}
